#!/usr/bin/env bash
LOG_DIR=/tmp/pekpp-view-watcher
LOG=${LOG_DIR}/events.log
DIR=/home/deploy/apps/pekpp/storage/framework/views
mkdir -p "$LOG_DIR"
echo "Watcher started $(date) PID=$$" >> "$LOG"

if command -v inotifywait >/dev/null 2>&1; then
  inotifywait -m -e create --format '%w%f %T' --timefmt '%F %T' "$DIR" | while read -r file time; do
    echo "EVENT $time $file" >> "$LOG"
    stat -c 'STAT %n %U %G %a %y' "$file" >> "$LOG" 2>&1 || true
    ls -lZ "$file" >> "$LOG" 2>&1 || true
    getfacl -p "$file" >> "$LOG" 2>&1 || true
    sleep 0.1
    echo "HEAD_START" >> "$LOG"
    head -n 40 "$file" >> "$LOG" 2>&1 || true
    echo "HEAD_END" >> "$LOG"
    echo "----" >> "$LOG"
  done
else
  echo "inotifywait not found, falling back to poll mode" >> "$LOG"
  KNOWN="$(ls -1 "$DIR" 2>/dev/null)"
  while true; do
    CURRENT="$(ls -1 "$DIR" 2>/dev/null)"
    for f in $CURRENT; do
      if ! echo "$KNOWN" | grep -xq "$f"; then
        file="$DIR/$f"
        echo "EVENT $(date '+%F %T') $file" >> "$LOG"
        stat -c 'STAT %n %U %G %a %y' "$file" >> "$LOG" 2>&1 || true
        ls -lZ "$file" >> "$LOG" 2>&1 || true
        getfacl -p "$file" >> "$LOG" 2>&1 || true
        echo "HEAD_START" >> "$LOG"
        head -n 40 "$file" >> "$LOG" 2>&1 || true
        echo "HEAD_END" >> "$LOG"
        echo "----" >> "$LOG"
      fi
    done
    KNOWN="$CURRENT"
    sleep 0.5
  done
fi
