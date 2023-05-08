#!/bin/bash
set -e

# Recommendation: setup and use your IDE to run and manage docker-compose.
# This file works, but main idea of it is just to show how to up the project properly.

bash "$(dirname "$0")"/../base/dockerForceBuild.env.sh test
