#!/bin/bash
#
# Script to bundle a new phar and public it on S3
#

VERSION=$1
[ -z $VERSION ] && echo "Missing version parameter" && git tag && exit 1

ARTIFACT="cloudflare.phar"
DOWNLOADABLE_NAME="cloudflare-cli-${VERSION}.phar"
LOCAL_NAME="cloudflare"
BUCKET="static.devops.it/cloudflare-cli"

[ -f $ARTIFACT ] && rm $ARTIFACT
git tag $VERSION

box build
[ ! -f $ARTIFACT ] && echo "\nMissing $ARTIFACT, build failed?\n" && exit 1

# Public file and clean current directory
s3cmd put $ARTIFACT s3://$BUCKET/$DOWNLOADABLE_NAME

# Create fragment for versions.json
SHA1=$(php -r "echo sha1_file('$ARTIFACT');")
echo -e "\n  {"
echo -e "    \"name\": \"${ARTIFACT}\","
echo -e "    \"sha1\": \"${SHA1}\","
echo -e "    \"url\": \"http://$BUCKET/$DOWNLOADABLE_NAME\","
echo -e "    \"version\": \"${VERSION}\""
echo -e "  }\n"

echo "Manually update versions.json, then continue"
read WAIT

git commit -m "released version ${VERSION}" versions.json
git push

rm $ARTIFACT
