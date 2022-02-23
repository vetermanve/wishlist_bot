#!/bin/bash -x
version='1.0'
dockerProject="vetermanve/wishlist"
commitCount=$(git rev-list --count HEAD);
branch=$(git rev-parse --abbrev-ref HEAD);

# composing array of tags
tags=()
if [[ 'main' == $branch ]]; then
  tags+=("$dockerProject:latest")
  tags+=("$dockerProject:$version.$commitCount")
else
  tags+=("$dockerProject:$version.$commitCount-$branch")
fi

#adding tag to command
tagsString=""
for tag in ${tags[@]}; do
  tagsString+=' -t '$tag' '
done

#building image
docker build $tagsString -f .docker/php-worker-production/Dockerfile .

#buising tags was built
for tag in ${tags[@]}; do
  docker push $tag
done