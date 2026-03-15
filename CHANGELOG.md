# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [1.0.0] - 2026-03-15

### Added

- `list_log_files` tool — discover available log files with metadata
- `search_logs` tool — search by text query and/or severity level with pagination
- `get_log_entry` tool — retrieve full log entry details including stack trace and context
- `get_error_summary` tool — high-level health overview with severity counts and recent errors
- Configuration file with `enabled`, `max_results`, and `max_log_text_length` options
- STDIO transport via `laravel/mcp`
- Support for `opcodesio/log-viewer` v3.x and v4.x
