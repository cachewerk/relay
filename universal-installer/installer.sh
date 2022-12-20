#!/bin/sh
set -e

# Main Environment variables

RELAY_VERSION="v0.5.1"
RELAY_KEY="${1:-0000}"
RELAY_STORAGE="https://cachewerk.s3.amazonaws.com/relay"
RELAY_GITHUB="https://github.com/cachewerk/relay"
RELAY_INSTALLER_URL="https://get.relay.so"

SUPPORTED_PHP="7.4 8.0 8.1 8.2"
SUPPORTED_OS="darwin debian ubuntu alpine centos amzn rocky"
SUPPORTED_ARCH="aarch64 x86-64"

RELAY_DISTRO=$(uname -s | tr '[:upper:]' '[:lower:]')
RELAY_ARCH="$(uname -m | sed -e 's/arm64/aarch64/;s/amd64\|x86_64/x86-64/;s/x86_64/x86-64/;')"

TMP_DIR="/tmp/relay"

# Helper functions
join_by() { local IFS="$1 "; shift; echo "$*"; }
command_exists() { command -v "$@" > /dev/null 2>&1; }

# Coloring

if [ -r /etc/os-release ] && [ ${RELAY_DISTRO} != 'darwin' ]; then
    RELAY_DISTRO="$(. /etc/os-release && echo "$ID")"
fi

if [ ! "${RELAY_DISTRO}" = "debian" ] && [ ! "${RELAY_DISTRO}" = "ubuntu" ] && [ ! "${RELAY_DISTRO}" = "darwin" ]  ; then
    alias echo='echo -e '
fi    

# Coloring
normal='\033[0m'

red='\033[0;31m ';
bold_red='\033[1;31m';

green='\033[0;32m';
bold_green='\033[1;32m';

yellow='\033[0;33m';
bold_yellow='\033[1;33m';


# Error/Success Prefix
error() { echo "${bold_red}[Error]${normal}: $@"; }
pass() { echo "${bold_green}[Pass]${normal}: $@"; }
info() { echo "${bold_yellow}[Info]${normal}: $@"; }

cleanup() {
    if [ -d ${TMP_DIR} ]; then
        rm -fr ${TMP_DIR}
    fi
}

# trap 'cleanup' 1 2 3 6

supported_os() {
    info "Checking your system, please wait"

    local supported_arch=$(echo $(echo "${SUPPORTED_ARCH}" | grep -c "${RELAY_ARCH}"))
    local supported_os=$(echo $(echo "${SUPPORTED_OS}" | grep -c "$RELAY_DISTRO"))

    if [ $supported_os -eq 0 ] || [ ${supported_arch} -eq 0 ]; then
        error "You system is not currently supported."
        error "Can report the following information to us at ${RELAY_GITHUB}/issues"
        error "Your system information:"
        error "Operating System: ${RELAY_DISTRO}"
        error "Architecture: ${RELAY_ARCH}"
        error "And we will try to add support as soon as possible."
        exit 1;
    fi

    if [ $RELAY_DISTRO = "darwin" ]; then
        RELAY_ARCH=$(uname -m | sed -e 's/x86_64/x86-64/;')
    fi

    pass "Your system is supported"
    pass "Operating System: ${RELAY_DISTRO}"
    pass "Architecture: ${RELAY_ARCH}"
}

is_php_supported() {
    info "Checking installed php version"

    if [ -z ${PHP_BINARY} ]; then

        if ! command_exists php; then
            error "PHP is not installed"
            exit 1;
        fi

        PHP_BINARY=`command -v php`

        if [ $? -gt 0 ]; then
            error "PHP is not installed, please make sure you install php first then run the script again."
            exit 1;
        fi    
    fi

    if [ ! -r ${PHP_BINARY} ]; then
        error "The php binary path that you have provided ${PHP_BINARY} does not exists"
        exit 1;
    fi

    PHP_VERSION="$(${PHP_BINARY}  -v | head -1 | awk -F " " '{print $2}' | cut -c -3)"
    PHP_INI_DIR="$(${PHP_BINARY} --ini | grep "Scan for additional"  | awk -F ": " '{print $2}' )"
    # MAIN_INI_DIR="$(echo ${PHP_INI_DIR} | awk -F "${PHP_VERSION}" '{print $1}')${PHP_VERSION}"
    PHP_EXT_DIR="$(${PHP_BINARY} -i | grep '^extension_dir' | awk -F " => " '{print $3}')"


    if [ -z "${PHP_BINARY}" ]; then
        error "PHP is not installed, please make sure you install php first then run the script again."
        exit 1;
    fi

    php_supported=$(echo ${SUPPORTED_PHP} | grep ${PHP_VERSION}) || true
    
    if [ -z "${php_supported}" ]; then
        error "You have PHP version ${PHP_VERSION} which is not supported"
        exit 1;
    fi

    pass "PHP is installed and supported"
    pass "PHP path: ${PHP_BINARY}"
    pass "PHP version: ${PHP_VERSION}"
    pass "PHP additional ini directory: ${PHP_INI_DIR}"
    pass "PHP extension director: ${PHP_EXT_DIR}"
}

has_required_extensions() {

    info "Checking the existance of the required php extenstions"

    extenstions=$(echo $(php -m | grep -E -c "json|msgpack|igbinary"))

    if [ $extenstions -lt 3 ]; then

        local to_install=""

        for ext in json msgpack igbinary; do

            local not_installed=$(echo $(php -m | grep -E -c "${ext}"))
            
            if [ $not_installed -eq 0 ]; then
                to_install="${to_install} ${ext}"
            fi

        done;

        error "Please make sure you have the installed the required extensions json, msgpack and igbinary"
        error "You need to install the following extensions: ${bold_yellow}$(join_by , $to_install).${normal}"

        exit 1;
    fi

    pass "Required PHP extensions (json, msgpack, igbinary) are installed"
}

openssl_version() {
  openssl version | awk -F " " '{print $2}' | cut -c 1-3 
}

prepare_distro_name() {

    VERSION_ID="$(. /etc/os-release && echo "$VERSION_ID")"

    case "$RELAY_DISTRO" in
        *ubuntu* ) RELAY_DISTRO=debian ;;
        *amzn* ) RELAY_DISTRO=centos7 ;;
        *rocky* ) 
            local version=$(echo ${VERSION_ID} | cut -c 1)
            if [ ${version} -lt 9 ]; then
                RELAY_DISTRO=centos8
            else
                TMP_DISTRO=centos8
            fi
         ;;
        *centos* ) RELAY_DISTRO=centos${VERSION_ID} ;;
    esac
}

openssl_check() {

    local version=0

    if command_exists openssl; then

        version=`openssl_version`

        if ([ "${RELAY_DISTRO}" = "centos7" ] || [ "${RELAY_DISTRO}" = "rocky" ]) && [ $(echo `ldconfig -p | grep -e "libssl.so.1.1" -e "libcrypto.so.1.1" -c`) -eq 2 ]; then
        
            version=1.1

            :
        
        elif (([ "${RELAY_DISTRO}" = "centos7" ] || [ "${RELAY_DISTRO}" = "rocky" ]) && [ $(echo `ldconfig -p | grep -e "libssl.so.1.1" -e "libcrypto.so.1.1" -c`) -ne 2 ]) || [ "${version}" = "1.0" ]; then
        
            error "You need to make sure that you have openssl 1.1 installed correctly."
            exit 1;            
        
        elif [ ! "${RELAY_DISTRO}" = "debian" ] && [ ! "${version}" = "1.1" ]; then
        
            error "You need to make sure that you have openssl 1.1 installed correctly."
            exit 1;
        
        fi

    elif ([ "${RELAY_DISTRO}" = "centos7" ] || [ "${RELAY_DISTRO}" = "rocky" ]) && [ `ldconfig -p | grep -e "libssl.so.1.1" -e "libcrypto.so.1.1" -c` -eq 2 ]; then

        version=1.1

        :

    else

        if [ "${RELAY_DISTRO}" = "rocky" ]; then
            error "Make sure you have openssl 1.1 installed."
        else
            error "Make sure you have openssl installed."
        fi
        exit 1;
    
    fi

    info "Openssl ${version} is installed."
}

required_libraries() {

    info "Checking the required libraries"
    
    cleanup
    
    mkdir ${TMP_DIR}

    if ! command_exists tar; then
        error "Tar is needed to decompress the extension file."
        exit 1;
    fi

    prepare_distro_name

    openssl_check

    if [ ! -z ${TMP_DISTRO} ]; then
        RELAY_DISTRO=${TMP_DISTRO}
    fi


    local relay_filename="relay-${RELAY_VERSION}-php${PHP_VERSION}-${RELAY_DISTRO}-${RELAY_ARCH}.tar.gz"

    local number_of_installed_libraries=0

    case "${RELAY_DISTRO}" in
        *alpine* )
            number_of_installed_libraries=$(echo `apk list -I | grep -e zstd-libs -e lz4-libs -c`)
            ;;
        **debian** )
            number_of_installed_libraries=$(echo `ldconfig -p | grep -e "lz4" -e "zstd" -c`)
            if [ $(openssl_version | cut -c 1) -eq 3 ]; then
                relay_filename="relay-${RELAY_VERSION}-php${PHP_VERSION}-${RELAY_DISTRO}-${RELAY_ARCH}%2Blibssl3.tar.gz"
            fi            
            ;;
        * )
            number_of_installed_libraries=$(echo `ldconfig -p | grep -e "lz4" -e "zstd" -c`)         
            ;;            
    esac

    if [ ${number_of_installed_libraries} -lt 2 ]; then      
        error "Make sure you have all the required libraries (lz4, zstd) installed."
        exit 1;
    fi

    local download_url="${RELAY_STORAGE}/${RELAY_VERSION}/${relay_filename}"

    info "downloading: ${download_url}"


    if command_exists curl; then
        local curl_output=$(curl -fsSL ${download_url} --output /tmp/${relay_filename}  > /dev/null 2>&1)
    elif command_exists wget; then
        local wget_output=$(wget -c ${download_url} -O /tmp/${relay_filename} > /dev/null 2>&1 )
    else
        error "This installer needs either curl or wegt to download the extension."
        exit 1;
    fi


    if [ $? -gt 0 ]; then
        error "We couldn't download the file, please make sure that you have access to the internet"
        error "and make sure you have curl or wget installed too."
        exit 1;
    fi

    local gzip_output=$(gzip -t /tmp/${relay_filename} > /dev/null 2>&1)

    if [ $? -gt 0 ] || [ ! -f "/tmp/${relay_filename}" ]; then
        error "The downloaded file seems to be corrupted, can you rerun the script and try again."
        exit 1;
    fi

    info "Extracting the file: /tmp/${relay_filename}"

    tar -xzvf /tmp/${relay_filename} --strip-components=1 -C ${TMP_DIR} > /dev/null 2>&1

    if [ $? -gt 0 ]; then
        error "An error happened while extracting the file."
        exit 1;
    fi

    # if [ "${RELAY_DISTRO}" = "darwin" ]; then
    #     has_missing_libraries=$(echo $(otool -L ${TMP_DIR}/relay-pkg.so | grep "not found" -c))
    #     missing_lib=$(otool -L ${TMP_DIR}/relay-pkg.so  | grep "not found" | awk -F'.dylib' '{print $1}' | awk -F '/' '{if($NF == "") print $(NF - 1); else print $NF}' | sed '1 d' | sed -e '/libSystem.B$/d')
    # else
    #     has_missing_libraries=$(echo $(ldd ${TMP_DIR}/relay-pkg.so 2>&1 /dev/null | grep "Error loading shared library" -c))
    #     missing_lib=$(ldd ${TMP_DIR}/relay-pkg.so 2>&1 /dev/null | grep "Error loading shared library" | awk -F"[ => ]" '{print $1}' | sed -e 's/\.[[:alpha:]]\{2\}\.[[:digit:]]\{1\}//g' | sed -e "s/[[:space:]]//g" | sed 's/\..\{1\}$//')
    # fi

    # if [ $has_missing_libraries -gt 0 ]; then
    #     error "$(join_by , $missing_lib) were not found on the system, please install them."
    #     exit 1;
    # fi

    pass "Required libraries are installed."
}

install() {

    info "Installing Relay extention"

    local INI_FILE_NAME="20-relay.ini"
    local UUID=""

    if [ $RELAY_DISTRO = "darwin" ]; then
        UUID=$(echo $(/usr/bin/uuidgen));
        LC_ALL=C sed -i.back "s/00000000-0000-0000-0000-000000000000/${UUID}/" ${TMP_DIR}/relay-pkg.so  > /dev/null 2>&1
    else
        UUID=$(cat /proc/sys/kernel/random/uuid)
        sed -i "s/00000000-0000-0000-0000-000000000000/${UUID}/" ${TMP_DIR}/relay-pkg.so  > /dev/null 2>&1
    fi
    
    if [ $? -gt 0 ]; then
        error "An error happened while injecting the mandatory UUID into the binary."
        exit 1;
    fi

    cp ${TMP_DIR}/relay-pkg.so ${PHP_EXT_DIR}/relay.so;

    if [ ! -r ${PHP_EXT_DIR}/relay.so ]; then
        error "Something went wrong while copying the file to ${bold_yellow}${PHP_EXT_DIR}/relay.so${normal}."
        error "The extension file can't be located there"
        exit 1;
    fi

    sed -i.bak 's/^;[[:space:]]relay.maxmemory =.*/relay.maxmemory = 128M/' ${TMP_DIR}/relay.ini
    sed -i.bak 's/^;[[:space:]]relay.eviction_policy =.*/relay.eviction_policy = lru/' ${TMP_DIR}/relay.ini
    sed -i.bak 's/^;[[:space:]]relay.environment =.*/relay.environment = production/' ${TMP_DIR}/relay.ini
    sed -i.bak "s/^;[[:space:]]relay.key =.*/relay.key = $RELAY_KEY/" ${TMP_DIR}/relay.ini

    local has_cli=$(echo $PHP_INI_DIR | grep 'cli' -c )

    if [ $has_cli -gt 0 ]; then
        local mods_avaliable="$(echo $PHP_INI_DIR | awk -F "/cli/" '{print $1}')/mods-available"
        
        local php_dir=$(echo $PHP_INI_DIR | awk -F "/cli/" '{print $1}')

        if [ -d $mods_avaliable ]; then
            cp ${TMP_DIR}/relay.ini $mods_avaliable

            for dir in $(ls -d ${php_dir}/*/); do
                if [ -d "${dir}/conf.d" ]; then
                    ln -sf ${mods_avaliable}/relay.ini ${dir}/conf.d/${INI_FILE_NAME}
                fi
            done
        fi
    else
        case "$RELAY_DISTRO" in
            *alpine* ) INI_FILE_NAME="60_relay.ini" ;;
            *centos7* ) INI_FILE_NAME="50-relay.ini" ;;
            *centos8* ) INI_FILE_NAME="50-relay.ini" ;;
            * ) INI_FILE_NAME="20-relay.ini" ;;
        esac

        cp ${TMP_DIR}/relay.ini $PHP_INI_DIR/${INI_FILE_NAME}
    fi

    if [ ! -r ${PHP_INI_DIR}/${INI_FILE_NAME} ]; then
        error "Something went wrong, can't find the ini file."
        exit 1;
    fi

    pass "Relay has been installed successfuly"
    info "[CLI test]"
    ${PHP_BINARY} --ri relay


    # Check to see if the UUID match
    ${PHP_BINARY} --ri relay | grep "${UUID}" > /dev/null;

    if [ $? -gt 0 ]; then
        error "The injected random UUID does not match"
        error "Something went wrong, please repot it at"
        error "${RELAY_GITHUB}/issues"
        exit 1;
    fi


    echo "\n"
    pass "You can edit the the configuration at: ${bold_yellow}${PHP_INI_DIR}/${INI_FILE_NAME}${normal}"
}

help() {
local help_message=`cat << EOL
You can run the script in 3 modes:
1) Interactive mode: where we ask you to confirm the php path that we found
${bold_yellow}curl -sfLS ${RELAY_INSTALLER_URL} | RELAY_KEY=<KEY> sh -- --interactive${normal}

2) Non-interactive mode: where you specify the php path to use when you run the script
${bold_yellow}curl -sfLS ${RELAY_INSTALLER_URL} | RELAY_KEY=<KEY> sh -- --php_path /usr/bin/php${normal}

3) Auto mode (default): where we search the filesystem to find the php installed and use it.
${bold_yellow}curl -sfLS ${RELAY_INSTALLER_URL} | RELAY_KEY=<KEY> sh${normal}


${bold_yellow}ENVIRONMENTS:${normal}
   RELAY_KEY                Your Relay Key

${bold_yellow}OPTIONS:${normal}
   --php_path value         Specifies the path for the local PHP binay.
   --interactive            Should we ask for confirmation before we use the PHP binary we found?

EOL
`

echo "${help_message}\n"

    exit 0;
}


setup() {
    while [ $# -gt 0 ]; do
        case "$1" in
            --help)
                help
                ;;
            --php_path)
                if [ -z ${2} ]; then
                    error "The php_path shuold not be empty"
                    exit 1;
                fi
                PHP_BINARY="$2";
                ;;

            --interactive)
                INTERACTIVE=1;
                ;;

            --*)
                error "Illegal option $1"
                exit 1
                ;;
        esac
        shift $(( $# > 0 ? 1 : 0 ))
    done
}

logo() {
cat << EOL

8888888b.          888                                        
888   Y88b         888                                        
888    888         888                                        
888   d88P .d88b.  888  8888b.  888  888    .d8888b   .d88b.  
8888888P" d8P  Y8b 888     "88b 888  888    88K      d88""88b 
888 T88b  88888888 888 .d888888 888  888    "Y8888b. 888  888 
888  T88b Y8b.     888 888  888 Y88b 888 d8b     X88 Y88..88P 
888   T88b "Y8888  888 "Y888888  "Y88888 Y8P 88888P'  "Y88P"  
                                     888                      
                                Y8b d88P                      
                                 "Y88P"                       


EOL

}


{
    logo
    setup "$@"
    supported_os
    echo "\n"
    is_php_supported
    echo "\n"
    has_required_extensions
    echo "\n"
    required_libraries
    echo "\n"
    install
}