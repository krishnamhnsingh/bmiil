# BMIIL WordPress Bridge

Connects Claude AI directly to `test.bmiil.com` via GitHub.

## How it works
```
Claude → writes JSON to /commands/pending.json
       → GitHub Action triggers
       → Action calls WordPress REST API  
       → Updates templates/pages live
       → Writes result to /results/latest.json
       → Claude reads result
```

## Structure
| Folder | Purpose |
|--------|---------|
| `/templates/` | Live Elementor snapshots pulled from WordPress |
| `/commands/` | Commands Claude queues for execution |
| `/results/` | Results written back by GitHub Action |
| `/.github/workflows/` | GitHub Actions automation |
