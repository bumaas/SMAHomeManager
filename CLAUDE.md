# SMAHomeManager — Projekt-Hinweise

IP-Symcon-Modul (`IPSModuleStrict`), das die Multicast-Datagramme eines SMA Home Managers /
SMA Energy Meters auswertet (UDP-Multicast 239.12.255.254, Port 9522) und die Messkanäle
als Statusvariablen bereitstellt.

## Struktur

- `SMAHomeManagerDevice/module.php` — einziges Modul (gesamte Logik)
- `SMAHomeManagerDevice/form.json` — Konfigurationsformular; englische Labels dienen als Übersetzungsschlüssel
- `SMAHomeManagerDevice/locale.json` — deutsche Übersetzungen
- `library.json` (Repo-Wurzel) — Version, Build, Datum (Build-Konvention siehe globale CLAUDE.md)
- `tests/check_locale.php` — Übersetzungs-Vollständigkeitscheck (läuft in der CI)

## Protokoll-Verarbeitung (SMA-Net)

- `processData()` zerlegt das Datagramm: Header-Prüfung (Protokoll-ID, Seriennummer),
  danach OBIS-Datenstrom im Tag-Length-Value-Format — die byte-genauen Offsets stehen
  als Kommentare direkt in der Funktion.
- Die Zuordnung OBIS-ID → Variable liefert `getLookupMap()`: dynamisch aus `MEASUREMENTS`
  aufgebaut, Einzelphasen über Typ-Offsets. Die Map hängt von den Properties ab
  (Detail-Kanäle, Einzelphasen) — Registrierung und Empfang nutzen dieselbe Map.
- Seriennummern-Filter: ohne konfigurierte Seriennummer werden die Pakete aller Geräte
  verarbeitet (keine Fixierung auf das erste Gerät); `SERIAL_NUMBER` zeigt die zuletzt
  empfangene Seriennummer.
- `GetConfigurationForParent()` konfiguriert den Multicast-Socket automatisch —
  Änderungen an Port/Multicast-IP nur dort.

## Variablennamen und Übersetzungen

- Die Anzeigenamen werden in `getModifiedName()` **dynamisch zusammengesetzt**
  (Prefix + übersetzter Basisname + „+"/„−"-Suffix; Summen-Sonderfälle wie
  „Grid Consumption"). Deshalb meldet `tests/check_locale.php` die locale-Schlüssel
  der Variablennamen als „verwaist (nur Hinweis)" — das ist korrekt so.
- Bei Textänderungen synchron halten: `form.json` (englischer Schlüssel) ↔
  `locale.json` (deutscher Text) ↔ ggf. `README.md`.

## Darstellungen

Keine globalen Profile; Variablen nutzen `VARIABLE_PRESENTATION_VALUE_PRESENTATION`
mit Suffix/Digits und teils Intervall-Umrechnung (kW/kWh) direkt aus `MEASUREMENTS`.
