-- ipu.co.in Search Console weekly snapshot schema
-- One row in `snapshots` per weekly export; child tables hold the 5 sheets.
-- UNIQUE(week_start, week_end) is the dedupe key; the same week cannot be
-- ingested twice without --force on the ingest script.

PRAGMA foreign_keys = ON;

CREATE TABLE IF NOT EXISTS snapshots (
    id           INTEGER PRIMARY KEY AUTOINCREMENT,
    week_start   DATE     NOT NULL,
    week_end     DATE     NOT NULL,
    source_file  TEXT     NOT NULL,
    imported_at  DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE(week_start, week_end)
);

CREATE TABLE IF NOT EXISTS sc_queries (
    snapshot_id  INTEGER NOT NULL,
    query        TEXT    NOT NULL,
    clicks       INTEGER NOT NULL,
    impressions  INTEGER NOT NULL,
    ctr          REAL    NOT NULL,
    position     REAL    NOT NULL,
    PRIMARY KEY (snapshot_id, query),
    FOREIGN KEY (snapshot_id) REFERENCES snapshots(id) ON DELETE CASCADE
);
CREATE INDEX IF NOT EXISTS idx_sc_queries_query ON sc_queries(query);

CREATE TABLE IF NOT EXISTS sc_pages (
    snapshot_id  INTEGER NOT NULL,
    page         TEXT    NOT NULL,
    clicks       INTEGER NOT NULL,
    impressions  INTEGER NOT NULL,
    ctr          REAL    NOT NULL,
    position     REAL    NOT NULL,
    PRIMARY KEY (snapshot_id, page),
    FOREIGN KEY (snapshot_id) REFERENCES snapshots(id) ON DELETE CASCADE
);
CREATE INDEX IF NOT EXISTS idx_sc_pages_page ON sc_pages(page);

CREATE TABLE IF NOT EXISTS sc_countries (
    snapshot_id  INTEGER NOT NULL,
    country      TEXT    NOT NULL,
    clicks       INTEGER NOT NULL,
    impressions  INTEGER NOT NULL,
    ctr          REAL    NOT NULL,
    position     REAL    NOT NULL,
    PRIMARY KEY (snapshot_id, country),
    FOREIGN KEY (snapshot_id) REFERENCES snapshots(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS sc_devices (
    snapshot_id  INTEGER NOT NULL,
    device       TEXT    NOT NULL,
    clicks       INTEGER NOT NULL,
    impressions  INTEGER NOT NULL,
    ctr          REAL    NOT NULL,
    position     REAL    NOT NULL,
    PRIMARY KEY (snapshot_id, device),
    FOREIGN KEY (snapshot_id) REFERENCES snapshots(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS sc_daily (
    snapshot_id  INTEGER NOT NULL,
    date         DATE    NOT NULL,
    clicks       INTEGER NOT NULL,
    impressions  INTEGER NOT NULL,
    ctr          REAL    NOT NULL,
    position     REAL    NOT NULL,
    PRIMARY KEY (snapshot_id, date),
    FOREIGN KEY (snapshot_id) REFERENCES snapshots(id) ON DELETE CASCADE
);
CREATE INDEX IF NOT EXISTS idx_sc_daily_date ON sc_daily(date);
