<?php

declare(strict_types=1);

/**
 * SMAHomeManagerDevice Klasse
 *
 * Dieses Modul wertet die Multicast-Datagramme eines SMA Home Managers oder SMA Energy Meters aus.
 * Die Daten werden per UDP (Standard-Port 9522) empfangen und in IP-Symcon Variablen geschrieben.
 */
class SMAHomeManagerDevice extends IPSModuleStrict
{
    // Konfigurations-Konstanten
    private const string MODID_MULTICAST_SOCKET          = '{BAB408E0-0A0F-48C3-B14E-9FB2FA81F66A}';
    private const string PROP_SERIAL_NUMBER              = 'SerialNumber';
    private const string PROP_SHOW_DETAILED_CHANNELS     = 'ShowDetailedChannels';
    private const string PROP_SHOW_SINGLE_PHASES         = 'ShowSinglePhases';
    private const string PROP_EXTENDED_UPDATE_INTERVAL   = 'ExtendedUpdateInterval';
    private const string PROP_ENTENDED_DEBUG_INFORMATION = 'ExtendedDebugInformation';

    private const array PRESENTATION_INTERVALS_KW = [
        [
            'ColorDisplay'     => -1,
            'IntervalMinValue' => 1000,
            'IntervalMaxValue' => 999999999,
            'ConstantActive'   => false,
            'ConstantValue'    => '',
            'ConversionFactor' => 1000,
            'PrefixActive'     => false,
            'PrefixValue'      => '',
            'SuffixActive'     => true,
            'SuffixValue'      => ' kW',
            'DigitsActive'     => true,
            'DigitsValue'      => 1,
            'IconActive'       => false,
            'IconValue'        => '',
            'ColorActive'      => false,
            'ColorValue'       => -1
        ]
    ];

    private const array PRESENTATION_INTERVALS_KWH = [
        [
            'ColorDisplay'        => -1,
            'IntervalMinValue'    => 0,
            'IntervalMaxValue'    => 1,
            'ConstantActive'      => false,
            'ConstantValue'       => '',
            'ConversionFactor'    => 0.001,
            'PrefixActive'        => false,
            'PrefixValue'         => '',
            'SuffixActive'        => true,
            'SuffixValue'         => ' Wh',
            'DigitsActive'        => false,
            'DigitsValue'         => 0,
            'IconActive'          => false,
            'IconValue'           => '',
            'ColorActive'         => false,
            'ColorValue'          => -1
        ]
    ];

    /**
     * Definition der Messwert-Struktur.
     * Basierend auf den SMA Protokoll-Spezifikationen (OBIS-ähnliche Struktur).
     * 'type_byte' identifiziert die Messart im Datenstrom.
     */
    private const array MEASUREMENTS = [
        '0400'           => [
            'name'      => 'Real Power +',
            'divisor'   => 10,
            'suffix'    => ' W',
            'intervals' => self::PRESENTATION_INTERVALS_KW,
            'digits'    => 1,
            'detail'    => false,
            'type_byte' => 1
        ],
        '0800'           => [
            'name'      => 'Counter Real Power +',
            'divisor'   => 3600000,
            'suffix'    => ' kWh',
            'intervals' => self::PRESENTATION_INTERVALS_KWH,
            'digits'    => 1,
            'detail'    => false,
            'type_byte' => 1
        ],
        '0400_neg'       => [
            'name'      => 'Real Power -',
            'divisor'   => 10,
            'suffix'    => ' W',
            'intervals' => self::PRESENTATION_INTERVALS_KW,
            'digits'    => 1,
            'detail'    => false,
            'type_byte' => 2
        ],
        '0800_neg'       => [
            'name'      => 'Counter Real Power -',
            'divisor'   => 3600000,
            'suffix'    => ' kWh',
            'intervals' => self::PRESENTATION_INTERVALS_KW,
            'digits'    => 1,
            'detail'    => false,
            'type_byte' => 2
        ],
        '0400_react_pos' => ['name' => 'Reactive Power +', 'divisor' => 10, 'suffix' => ' var', 'digits' => 1, 'detail' => true, 'type_byte' => 3],
        '0800_react_pos' => [
            'name'      => 'Counter Reactive Power +',
            'divisor'   => 3600000,
            'suffix'    => ' kvarh',
            'digits'    => 3,
            'detail'    => true,
            'type_byte' => 3
        ],
        '0400_react_neg' => ['name' => 'Reactive Power -', 'divisor' => 10, 'suffix' => ' var', 'digits' => 1, 'detail' => true, 'type_byte' => 4],
        '0800_react_neg' => [
            'name'      => 'Counter Reactive Power -',
            'divisor'   => 3600000,
            'suffix'    => ' kvarh',
            'digits'    => 3,
            'detail'    => true,
            'type_byte' => 4
        ],
        '0400_app_pos'   => ['name' => 'Apparent Power +', 'divisor' => 10, 'suffix' => ' VA', 'digits' => 1, 'detail' => true, 'type_byte' => 9],
        '0800_app_pos'   => [
            'name'      => 'Counter Apparent Power +',
            'divisor'   => 3600000,
            'suffix'    => ' kVAh',
            'digits'    => 3,
            'detail'    => true,
            'type_byte' => 9
        ],
        '0400_app_neg'   => ['name' => 'Apparent Power -', 'divisor' => 10, 'suffix' => ' VA', 'digits' => 1, 'detail' => true, 'type_byte' => 10],
        '0800_app_neg'   => [
            'name'      => 'Counter Apparent Power -',
            'divisor'   => 3600000,
            'suffix'    => ' kVAh',
            'digits'    => 3,
            'detail'    => true,
            'type_byte' => 10
        ],
        '0400_fac'       => ['name' => 'Power Factor', 'divisor' => 1000, 'suffix' => '', 'digits' => 3, 'detail' => true, 'type_byte' => 13],
    ];

    private const int POSITION_STEP = 10;

    public function Create(): void
    {
        // Initialisierung der Instanz-Eigenschaften
        parent::Create();
        $this->RegisterPropertyString(self::PROP_SERIAL_NUMBER, '');
        $this->RegisterPropertyBoolean(self::PROP_SHOW_DETAILED_CHANNELS, false);
        $this->RegisterPropertyBoolean(self::PROP_SHOW_SINGLE_PHASES, false);
        $this->RegisterPropertyInteger(self::PROP_EXTENDED_UPDATE_INTERVAL, 0);
        $this->RegisterPropertyBoolean(self::PROP_ENTENDED_DEBUG_INFORMATION, false);
    }

    public function GetCompatibleParents(): string
    {
        // Definiert, dass dieses Modul an einen Multicast-Socket (UDP) angeschlossen werden möchte
        return json_encode([
                               'type'      => 'connect',
                               'moduleIDs' => [self::MODID_MULTICAST_SOCKET]
                           ],
                           JSON_THROW_ON_ERROR);
    }

    public function ApplyChanges(): void
    {
        // Wird aufgerufen, wenn die Konfiguration im UI gespeichert wird
        $this->RegisterVariables();
        $this->SetStatus(IS_ACTIVE);
        parent::ApplyChanges();
    }

    private function RegisterVariables(): void
    {
        // Registriert alle Variablen basierend auf der dynamischen Lookup-Map
        $lookup = $this->getLookupMap();
        foreach ($lookup as $entry) {
            $config = $entry['config'];
            if (!$config['detail'] || $this->ReadPropertyBoolean(self::PROP_SHOW_DETAILED_CHANNELS)) {
                $ident = $this->getIdent($entry['prefix'], $config['name']);
                $name  = $this->getModifiedName($entry['prefix'], $config['name']);

                // Aufbau der modernen Darstellungs-Parameter
                $presentation = [
                    'PRESENTATION' => VARIABLE_PRESENTATION_VALUE_PRESENTATION
                ];

                if (isset($config['suffix'])) {
                    $presentation['SUFFIX'] = $config['suffix'];
                }

                if (isset($config['digits'])) {
                    $presentation['DIGITS'] = $config['digits'];
                }

                if (isset($config['intervals'])) {
                    $presentation['INTERVALS']        = json_encode($config['intervals'], JSON_THROW_ON_ERROR);
                    $presentation['INTERVALS_ACTIVE'] = true;
                }

                $this->RegisterVariableFloat($ident, $name, $presentation, $entry['pos']);
            }
        }

        // Frequenz mit moderner Darstellung
        $this->RegisterVariableFloat($this->getIdent('SUM', 'Network Frequency'), $this->Translate('Network Frequency'), [
            'PRESENTATION' => VARIABLE_PRESENTATION_VALUE_PRESENTATION,
            'SUFFIX'       => ' Hz',
            'DIGITS'       => 2
        ],                           150);

        $this->RegisterVariableString('SW_VERSION', $this->Translate('SW-Version'), '', 1000);
        $this->RegisterVariableString('SERIAL_NUMBER', $this->Translate('Serial Number'), '', 1010);
    }

    private function getLookupMap(): array
    {
        // Generiert eine Map der OBIS-Identifier (als Hex-String) zu Konfigurationsdaten
        $map = [];
        $pos = 10;

        // Summen-Werte (Gesamtverbrauch/-einspeisung über alle Phasen)
        foreach (self::MEASUREMENTS as $key => $m) {
            $id       = '00' . str_pad(dechex($m['type_byte']), 2, '0', STR_PAD_LEFT) . (str_contains($key, '0800') ? '0800' : '0400');
            $map[$id] = ['prefix' => 'SUM', 'config' => $m, 'pos' => $pos];
            $pos      += self::POSITION_STEP;
        }
        $map['000e0400'] = [
            'prefix' => 'SUM',
            'config' => ['name' => 'Network Frequency', 'divisor' => 1000, 'profile' => '~Hertz.50', 'detail' => false],
            'pos'    => 150
        ];

        // Einzelphasen-Werte (L1, L2, L3) falls in den Instanzeinstellungen aktiviert
        if ($this->ReadPropertyBoolean(self::PROP_SHOW_SINGLE_PHASES)) {
            $phases = [
                'L1' => ['offset' => 20, 'pos' => 300, 'p_id' => '1f', 'v_id' => '20'],
                'L2' => ['offset' => 40, 'pos' => 500, 'p_id' => '33', 'v_id' => '34'],
                'L3' => ['offset' => 60, 'pos' => 700, 'p_id' => '47', 'v_id' => '48']
            ];
            foreach ($phases as $prefix => $pInfo) {
                $pos = $pInfo['pos'];
                foreach (self::MEASUREMENTS as $key => $m) {
                    $typeByte = str_pad(dechex($m['type_byte'] + $pInfo['offset']), 2, '0', STR_PAD_LEFT);
                    $id       = '00' . $typeByte . (str_contains($key, '0800') ? '0800' : '0400');
                    $map[$id] = ['prefix' => $prefix, 'config' => $m, 'pos' => $pos];
                    $pos      += self::POSITION_STEP;
                }

                // Die IDs für Strom und Spannung sind im Protokoll fest definiert (OBIS 31, 32, 51, 52, 71, 72 dezimal)
                $typePower   = str_pad(dechex($pInfo['offset'] + 11), 2, '0', STR_PAD_LEFT); // OBIS 31, 51, 71
                $typeVoltage = str_pad(dechex($pInfo['offset'] + 12), 2, '0', STR_PAD_LEFT); // OBIS 32, 52, 72
                $typeFactor  = str_pad(dechex($pInfo['offset'] + 13), 2, '0', STR_PAD_LEFT); // OBIS 33, 53, 73

                $map['00' . $typePower . '0400']   = [
                    'prefix' => $prefix,
                    'config' => ['name' => 'Power', 'divisor' => 1000, 'suffix' => ' A',
                                 'digits' => 3, 'detail' => false],
                    'pos'    => $pos + 10
                ];
                $map['00' . $typeVoltage . '0400'] = [
                    'prefix' => $prefix,
                    'config' => ['name' => 'Voltage', 'divisor' => 1000, 'suffix' => ' V', 'digits' => 2, 'detail' => false],
                    'pos'    => $pos + 20
                ];
                $map['00' . $typeFactor . '0400']  = [
                    'prefix' => $prefix,
                    'config' => ['name' => 'Power Factor', 'divisor' => 1000, 'digits' => 3, 'detail' => true],
                    'pos'    => $pos + 30
                ];
            }
        }
        return $map;
    }

    public function ReceiveData($JSONString): string
    {
        // Verarbeitet die vom Parent-Socket eintreffenden Datenpakete
        $interval = $this->ReadPropertyInteger(self::PROP_EXTENDED_UPDATE_INTERVAL);
        if ($interval > 0) {
            // Prüfung des Aktualisierungsintervalls zur Drosselung der Datenflut
            $last = (int)$this->GetBuffer('LastUpdate');
            if ($last > (time() - $interval)) {
                return '';
            }
            $this->SetBuffer('LastUpdate', (string)time());
        }

        $data = json_decode($JSONString, true, 512, JSON_THROW_ON_ERROR);
        $this->processData($data['Buffer']);
        return '';
    }

    /**
     * Verarbeitet die empfangenen RAW-Daten des SMA Geräts.
     * Das Paket wird nach dem SMA-Net-Protokoll (Header + OBIS-Datenstrom) zerlegt.
     *
     * @param string $hraw Der hex-kodierte Datenstrom vom Multicast-Socket.
     *
     * @return void
     */
    private function processData(string $hraw): void
    {
        // Debugging-Ausgabe des gesamten Hex-Strings zur Fehleranalyse
        if ($this->ReadPropertyBoolean(self::PROP_ENTENDED_DEBUG_INFORMATION)) {
            $this->SendDebug('RAW', $hraw, 0);
        }

        // Mindestlänge prüfen (SMA Header ist mind. 28 Bytes = 56 Zeichen lang)
        if (strlen($hraw) < 56) {
            return;
        }

        // Die Seriennummer steht laut Doku im Header (4 Bytes ab Byte 20)
        // Byte 18-19: SUSy ID (z.B. 270 = 010e), Byte 20-23: Seriennummer
        $serialHex     = substr($hraw, 20 * 2, 4 * 2);
        $currentSerial = (string)hexdec($serialHex);

        // Filter: Nur verarbeiten, wenn die Seriennummer übereinstimmt (oder keine konfiguriert ist)
        $configuredSerial = $this->ReadPropertyString(self::PROP_SERIAL_NUMBER);
        if ($configuredSerial !== '' && $configuredSerial !== $currentSerial) {
            return;
        }

        // Wenn noch keine Seriennummer konfiguriert war, setzen wir sie einmalig zur Info
        $this->SetValue('SERIAL_NUMBER', $currentSerial);

        // SMA Protokoll 6069 Check (SMA Net): Andere Protokoll-IDs werden ignoriert
        $protokollID = strtolower(substr($hraw, 32, 4));
        if ($protokollID !== '6069') {
            return;
        }

        $lookup = $this->getLookupMap();
        $offset = 28; // Datenbereich beginnt nach dem SMA Header

        // Iteration durch den Datenstrom (Tag-Length-Value Format)
        while ($offset < strlen($hraw) / 2) {
            $id = strtolower(substr($hraw, $offset * 2, 8));
            // Ende des Pakets oder ungültige ID erreicht
            if ($id === '00000000' || strlen($id) < 8) {
                break;
            }

            $offset += 4;
            $valLen = hexdec(substr($id, 4, 2)); // Länge des Werts (z.B. 4 Bytes für Momentanwerte, 8 für Zähler)

            if (isset($lookup[$id])) {
                // Wert aus Hex extrahieren
                $entry  = $lookup[$id];
                $config = $entry['config'];

                // Nur speichern, wenn die Variable auch wirklich existiert
                // (Detail-Variablen sind nur vorhanden, wenn die Eigenschaft gesetzt ist)
                if (!$config['detail'] || $this->ReadPropertyBoolean(self::PROP_SHOW_DETAILED_CHANNELS)) {
                    $ident    = $this->getIdent($entry['prefix'], $config['name']);
                    $hexValue = substr($hraw, $offset * 2, $valLen * 2);
                    $this->SetValue($ident, hexdec($hexValue) / $config['divisor']);
                }
            } elseif ($id === '90000000') {
                // Spezialbehandlung für Software-Version
                $sw    = substr($hraw, $offset * 2, 8);
                $swStr = sprintf(
                    '%d.%d.%d.%s',
                    hexdec(substr($sw, 0, 2)),
                    hexdec(substr($sw, 2, 2)),
                    hexdec(substr($sw, 4, 2)),
                    chr(hexdec(substr($sw, 6, 2)))
                );
                $this->SetValue('SW_VERSION', $swStr);
            }
            $offset += $valLen;
        }
    }

    private function getIdent(string $prefix, string $name): string
    {
        // Erzeugt einen gültigen IPS-Ident aus Prefix und Name
        $name = str_replace(['+', '-'], ['pos', 'neg'], $name);
        return $prefix . '_' . preg_replace('/[^a-z0-9_]/i', '_', $name);
    }

    private function getModifiedName(string $prefix, string $name): string
    {
        // Formatiert den Anzeigenamen der Variable und übersetzt ihn
        $suffix = match (true) {
            str_ends_with($name, ' +') => ' +',
            str_ends_with($name, ' -') => ' -',
            default => ''
        };

        $cleaned = $suffix === '' ? $name : substr($name, 0, -strlen($suffix));
        $trans   = $this->Translate($cleaned);

        $parts = ($prefix === 'SUM') ? [$trans, $suffix] : [$prefix, $trans . $suffix];

        return implode(' ', array_filter($parts, 'strlen'));
    }

    public function GetConfigurationForParent(): string
    {
        // Setzt beim Verbinden automatisch die korrekten Multicast-Parameter im UDP-Socket
        return json_encode([
                               'Host'               => '',
                               'Port'               => 0,
                               'BindPort'           => 9522,
                               'MulticastIP'        => '239.12.255.254',
                               'EnableBroadcast'    => false,
                               'EnableReuseAddress' => false,
                               'EnableLoopback'     => false
                           ],
                           JSON_THROW_ON_ERROR);
    }
}