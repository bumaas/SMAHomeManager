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

- `processData()` zerlegt das Datagramm: 28-Byte-Header (Seriennummer ab Byte 20,
  Protokoll-ID 0x6069 ab Byte 16), danach OBIS-Datenstrom im Tag-Length-Value-Format;
  das 3. Byte der OBIS-ID ist die Wertlänge (4 Bytes → `unpack('N')`, 8 Bytes → `unpack('J')`).
- Die Zuordnung OBIS-ID → Variable liefert `getLookupMap()`: dynamisch aus `MEASUREMENTS`
  aufgebaut; Einzelphasen entstehen über Typ-Offsets (+20/+40/+60 für L1/L2/L3),
  Strom/Spannung über Offset +11/+12. Die Map hängt von den Properties ab
  (Detail-Kanäle, Einzelphasen) — Registrierung und Empfang nutzen dieselbe Map.
- Seriennummern-Filter: ohne konfigurierte Seriennummer wird das erste empfangene Gerät
  übernommen (`SERIAL_NUMBER` wird zur Info gesetzt).
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
