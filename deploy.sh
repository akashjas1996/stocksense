#!/usr/bin/env bash
# =============================================================================
# StockSense — GCP Cloud Run deploy script
#
# First-time setup (run once):
#   1. Install gcloud CLI: https://cloud.google.com/sdk/docs/install
#   2. gcloud auth login
#   3. gcloud auth configure-docker REGION-docker.pkg.dev
#   4. Create Cloud SQL instance and database (see instructions below)
#   5. Store secrets in Secret Manager (see below)
#
# Usage:
#   export GCP_PROJECT=your-project-id
#   export GCP_REGION=asia-south1          # pick nearest: us-central1, europe-west1, etc.
#   export CLOUD_SQL_INSTANCE=stocksense   # your Cloud SQL instance name
#   ./deploy.sh
# =============================================================================
set -euo pipefail

# ── Config ────────────────────────────────────────────────────────────────────
PROJECT="${GCP_PROJECT:?Set GCP_PROJECT to your project ID}"
REGION="${GCP_REGION:-asia-south1}"
SQL_INSTANCE="${CLOUD_SQL_INSTANCE:-stocksense}"
SERVICE="stocksense"
REPO="stocksense"
IMAGE="${REGION}-docker.pkg.dev/${PROJECT}/${REPO}/${SERVICE}"

# ── Helpers ───────────────────────────────────────────────────────────────────
info()  { echo -e "\033[1;34m▶ $*\033[0m"; }
ok()    { echo -e "\033[1;32m✔ $*\033[0m"; }
die()   { echo -e "\033[1;31m✘ $*\033[0m" >&2; exit 1; }

# ── Pre-flight ────────────────────────────────────────────────────────────────
command -v gcloud >/dev/null || die "gcloud not installed. https://cloud.google.com/sdk/docs/install"
command -v docker  >/dev/null || die "docker not installed."

gcloud config set project "${PROJECT}" --quiet
gcloud services enable \
    run.googleapis.com \
    sqladmin.googleapis.com \
    artifactregistry.googleapis.com \
    secretmanager.googleapis.com \
    --quiet

# ── Artifact Registry repo (idempotent) ───────────────────────────────────────
info "Ensuring Artifact Registry repo '${REPO}' exists..."
gcloud artifacts repositories describe "${REPO}" \
    --location="${REGION}" --quiet 2>/dev/null \
|| gcloud artifacts repositories create "${REPO}" \
    --repository-format=docker \
    --location="${REGION}" \
    --description="StockSense container images" \
    --quiet
ok "Artifact Registry ready"

# ── Build & push ──────────────────────────────────────────────────────────────
info "Building image: ${IMAGE}"
docker build --platform linux/amd64 -t "${IMAGE}" .

info "Pushing image..."
docker push "${IMAGE}"
ok "Image pushed"

# ── Cloud SQL connection string ───────────────────────────────────────────────
SQL_CONNECTION="${PROJECT}:${REGION}:${SQL_INSTANCE}"

# ── Deploy to Cloud Run ───────────────────────────────────────────────────────
info "Deploying to Cloud Run (region: ${REGION})..."

# Secrets must already exist in Secret Manager:
#   stocksense-db-name, stocksense-db-user, stocksense-db-pass, stocksense-gemini-key
# Create them once with:
#   echo -n "stocksense"        | gcloud secrets create stocksense-db-name --data-file=-
#   echo -n "stocksense_user"   | gcloud secrets create stocksense-db-user --data-file=-
#   echo -n "yourpassword"      | gcloud secrets create stocksense-db-pass  --data-file=-
#   echo -n "AIzaSy..."         | gcloud secrets create stocksense-gemini-key --data-file=-

APP_URL=$(gcloud run services describe "${SERVICE}" \
    --region="${REGION}" --format="value(status.url)" 2>/dev/null || true)

gcloud run deploy "${SERVICE}" \
    --image="${IMAGE}" \
    --platform=managed \
    --region="${REGION}" \
    --allow-unauthenticated \
    --port=8080 \
    --memory=256Mi \
    --cpu=1 \
    --min-instances=0 \
    --max-instances=3 \
    --add-cloudsql-instances="${SQL_CONNECTION}" \
    --set-env-vars="DB_HOST=/cloudsql/${SQL_CONNECTION},APP_NAME=StockSense" \
    --set-secrets="\
DB_NAME=stocksense-db-name:latest,\
DB_USER=stocksense-db-user:latest,\
DB_PASS=stocksense-db-pass:latest,\
GEMINI_API_KEY=stocksense-gemini-key:latest" \
    --quiet

# Grab the live URL and set it as APP_URL
APP_URL=$(gcloud run services describe "${SERVICE}" \
    --region="${REGION}" --format="value(status.url)")

info "Setting APP_URL=${APP_URL}"
gcloud run services update "${SERVICE}" \
    --region="${REGION}" \
    --update-env-vars="APP_URL=${APP_URL}" \
    --quiet

ok "Deployed! → ${APP_URL}"
