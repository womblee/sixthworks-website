CREATE TABLE accounts (
  username TEXT,
  email TEXT,
  verified INT DEFAULT 0,
  created DATE,
  hashed_password TEXT,
  enc_key TEXT,
  iv TEXT,
  ip_logs TEXT DEFAULT 'null',
  hwid TEXT DEFAULT 'null',
  hwid_update INT DEFAULT 0,
  games TEXT,
  moderator INT DEFAULT 0
);

CREATE TABLE email_verification (
  email TEXT,
  code TEXT
);

CREATE TABLE requests (
  ip TEXT,
  time INT,
  agent TEXT
);

CREATE TABLE requests_reset (
  email TEXT,
  code TEXT
);

CREATE TABLE requests_purchase (
  username TEXT,
  method TEXT,
  wallet TEXT,
  game TEXT
);

CREATE TABLE redeem_keys (
  code TEXT,
  version TEXT,
  game TEXT
);
