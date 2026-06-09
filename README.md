# Sweetwater Comments Report

A small PHP application built for the Sweetwater web programmer test. It works with a
table of order comments and does two things:

1. **Comments report** — displays every comment, grouped into sections (candy,
   call me / don't call me, who referred me, signature requirements on delivery,
   and miscellaneous).
2. **Ship-date backfill** — parses the "Expected Ship Date" out of each comment's
   text and writes it into the `shipdate_expected` column.

Built with plain PHP and PDO — no frameworks or third-party packages.

> Setup and run instructions are added further down as the project comes together.

The original test brief is preserved in [TEST_INSTRUCTIONS.md](TEST_INSTRUCTIONS.md).
