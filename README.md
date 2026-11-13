# Peptides-R-Us — Opeptides WordPress Plugin

An internal-use WordPress plugin: a peptide encyclopaedia, a Convipen 3 ml cartridge
calculator, a per-user stock &amp; cycle diary, and a liability disclaimer injected
into every plugin page.

## Install

1. Copy the `opeptides/` folder into `wp-content/plugins/`.
2. Activate **Opeptides** in Plugins.
3. Activation creates the `wp_opeptides_diary` table, registers the `peptide`
   post type, and seeds seven starter encyclopaedia entries (BPC-157, TB-500,
   Ipamorelin, CJC-1295, GHK-Cu, PT-141, Semax).

## Usage

Create pages and drop in these shortcodes:

| Shortcode | What it renders |
|---|---|
| `[opeptide_encyclopaedia]` | Searchable peptide encyclopaedia grid |
| `[opeptide_calc]` | Convipen 3 ml cartridge calculator |
| `[opeptide_diary]` | Per-user stock tracker, dose log, and suggested cycles |
| `[opeptide_disclaimer]` | Liability banner (placed manually) |

The liability banner is automatically injected at the top of any page containing
an Opeptides shortcode. Enable **Peptides → Settings → Sitewide disclaimer** to show
it on every page of the site. The banner text is editable there too, along with the
cartridge volume and the suggested cycle templates.

Manage encyclopaedia entries under **Peptides** in the admin (title, description,
and data fields: formula, molecular weight, half-life, reconstitution, storage,
reported dose, side effects, Convipen dial notes).

## Calculator maths

Given peptide mass (mg) and solution volume (ml):

- concentration (mcg/ml) = mass × 1000 ÷ volume
- dose volume (ml) = target dose (mcg) ÷ concentration
- cartridge life = volume ÷ dose volume ÷ doses per day
- also shows the dose delivered at a 0.1 ml dial increment

## Diary

Each logged-in user gets a private diary (entries are keyed to `user_id`):

- **Stock** — peptide name + reconstituted volume in ml, with a running total of
  what's on hand.
- **Dose log** — date, peptide, dose in mcg, injected volume, and free-text notes.
- **Cycles** — one-click "start this cycle" from the admin-defined suggested
  templates.

## Liability

The disclaimer text defaults to: *"This site is for internal educational and
research reference only. Nothing here is medical advice, and no content should be
taken as encouragement to use any substance. Don't do drugs. If you ignore that,
that's on you — the authors accept no liability whatsoever."*

Edit it under **Peptides → Settings**.
