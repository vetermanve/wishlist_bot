#!/bin/bash -x
version='1.0'
dockerProject="vetermanve/wishlist"
commitCount=$(git rev-list --count HEAD);
branch=$(git rev-parse --abbrev-ref HEAD);

if [[ 'main' == $branch ]]; then
  tags=" -t $dockerProject:$version.$commitCount -t $dockerProject:latest "
else
  tags=" -t $dockerProject:$version.$commitCount-$branch "
fi

docker build $tags -f .docker/php-worker-production/Dockerfile .
docker push