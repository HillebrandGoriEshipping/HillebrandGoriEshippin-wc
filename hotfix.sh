#!/bin/bash

# https://learn.microsoft.com/en-us/cli/azure/boards/work-item?view=azure-cli-latest#az-boards-work-item-create
# is jq installed?
if ! command -v jq &> /dev/null; then
  echo "⚠️ jq is not installed. Installing..."

  # Detect OS
  if [[ "$OSTYPE" == "linux-gnu"* ]]; then
    if command -v apt &> /dev/null; then
      sudo apt update && sudo apt install -y jq
    elif command -v yum &> /dev/null; then
      sudo yum install -y jq
    else
      echo "❌ Package manager not detected. Install jq manually."
      exit 1
    fi
  elif [[ "$OSTYPE" == "darwin"* ]]; then
    if command -v brew &> /dev/null; then
      brew install jq
    else
      echo "❌ Homebrew not detected. Install it with : /bin/bash -c \"\$(curl -fsSL https://raw.githubusercontent.com/Homebrew/install/HEAD/install.sh)\""
      exit 1
    fi
  else
    echo "❌ OS not recognized. Install jq manually."
    exit 1
  fi

  echo "✅ jq installed successfully."
fi
TITLE=""
TYPE="BUG"
DESCRIPTION=""
FROM_BRANCH="production"

# Parse named arguments
while [[ $# -gt 0 ]]; do
  case "$1" in
    --title)
      TITLE="$2"
      shift 2
      ;;
    --type)
      TYPE="$2"
      shift 2
      ;;
    --from-branch)
      FROM_BRANCH="$2"
      shift 2
      ;;
    --description)
      DESCRIPTION="$2"
      shift 2
      ;;
    *)
      echo "❌ Unknown option: $1"
      echo "Usage: $0 --title \"TITLE\" [--type \"TYPE\"] [--description \"DESCRIPTION\"]"
      exit 1
      ;;
  esac
done

# Check required argument
if [[ -z "$TITLE" ]]; then
  echo "❌ --title is required"
  exit 1
fi

if [[ -z "$DESCRIPTION" ]]; then
  echo "❌ --description is required"
  exit 1
fi

# get AZ_PROJECT, AZ_AREA, AZ_ORG from .env or .env.local
if [[ -f .env.local ]]; then
echo "Using .env.local"
  export $(grep -v '^#' .env.local | xargs)
elif [[ -f .env ]]; then
  echo "Using .env"
  export $(grep -v '^#' .env | xargs)
fi
echo "Using AZ_PROJECT=$AZ_PROJECT, AZ_AREA=$AZ_AREA, AZ_ORG=$AZ_ORG"
if [[ -z "$AZ_PROJECT" || -z "$AZ_AREA" || -z "$AZ_ORG" ]]; then
  echo "❌ AZ_PROJECT, AZ_AREA or AZ_ORG is not set in .env"
  exit 1
fi

# get the returned json object
JSON_OUT=$(az boards work-item create --title "$TITLE" \
 --type "$TYPE" \
 --description "$DESCRIPTION" \
 --area "$AZ_AREA" \
 --project "$AZ_PROJECT" \
 --org "$AZ_ORG")

TICKET_ID=$(echo "$JSON_OUT" | jq -r '.id')

echo "Created ticket #$TICKET_ID"

# slugify the title
SLUG=$(echo "$TITLE" | tr '[:upper:]' '[:lower:]' | tr -cd '[:alnum:] _-' | tr ' ' '-')
# create the branch name 
BRANCH_NAME="hotfix/$TICKET_ID-$SLUG"
echo "Branch name : $BRANCH_NAME"

# create the branch from the specified branch
git checkout "$FROM_BRANCH"
git pull origin "$FROM_BRANCH"
git checkout -b "$BRANCH_NAME"
git push origin "$BRANCH_NAME"

echo "Work Item Url : https://dev.azure.com/jfhillebrand/Development/_workitems/edit/$TICKET_ID"