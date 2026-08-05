<?php

declare(strict_types=1);

/**
 * SMAHomeManagerDevice Klasse
 *
 * Dieses Modul wertet die Multicast-Datagramme eines SMA Home Managers oder SMA Energy Meters aus.
 * Die Daten werden per UDP (Standard-Port 9522) empfangen und in IP-Symcon Variablen geschrieben.
 */
enum MeasurementType: int
{
    case RealPowerPositive     = 1;
    case RealPowerNegative     = 2;
    case ReactivePowerPositive = 3;
    case ReactivePowerNegative = 4;
    case ApparentPowerPositive = 9;
    case ApparentPowerNegative = 10;
    case PowerFactor           = 13;
}

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
     */
    private const array MEASUREMENTS = [
        '0400'           => [
            'name'      => 'Real Power +',
            'divisor'   => 10,
            'suffix'    => ' W',
            'intervals' => self::PRESENTATION_INTERVALS_KW,
            'digits'    => 1,
            'detail'    => false,
            'type'      => MeasurementType::RealPowerPositive
        ],
        '0800'           => [
            'name'      => 'Counter Real Power +',
            'divisor'   => 3600000,
            'suffix'    => ' kWh',
            'intervals' => self::PRESENTATION_INTERVALS_KWH,
            'digits'    => 1,
            'detail'    => false,
            'type'      => MeasurementType::RealPowerPositive
        ],
        '0400_neg'       => [
            'name'      => 'Real Power -',
            'divisor'   => 10,
            'suffix'    => ' W',
            'intervals' => self::PRESENTATION_INTERVALS_KW,
            'digits'    => 1,
            'detail'    => false,
            'type'      => MeasurementType::RealPowerNegative
        ],
        '0800_neg'       => [
            'name'      => 'Counter Real Power -',
            'divisor'   => 3600000,
            'suffix'    => ' kWh',
            'intervals' => self::PRESENTATION_INTERVALS_KW,
            'digits'    => 1,
            'detail'    => false,
            'type'      => MeasurementType::RealPowerNegative
        ],
        '0400_react_pos' => ['name' => 'Reactive Power +', 'divisor' => 10, 'suffix' => ' var', 'digits' => 1, 'detail' => true, 'type' => MeasurementType::ReactivePowerPositive],
        '0800_react_pos' => [
            'name'      => 'Counter Reactive Power +',
            'divisor'   => 3600000,
            'suffix'    => ' kvarh',
            'digits'    => 3,
            'detail'    => true,
            'type'      => MeasurementType::ReactivePowerPositive
        ],
        '0400_react_neg' => ['name' => 'Reactive Power -', 'divisor' => 10, 'suffix' => ' var', 'digits' => 1, 'detail' => true, 'type' => MeasurementType::ReactivePowerNegative],
        '0800_react_neg' => [
            'name'      => 'Counter Reactive Power -',
            'divisor'   => 3600000,
            'suffix'    => ' kvarh',
            'digits'    => 3,
            'detail'    => true,
            'type'      => MeasurementType::ReactivePowerNegative
        ],
        '0400_app_pos'   => ['name' => 'Apparent Power +', 'divisor' => 10, 'suffix' => ' VA', 'digits' => 1, 'detail' => true, 'type' => MeasurementType::ApparentPowerPositive],
        '0800_app_pos'   => [
            'name'      => 'Counter Apparent Power +',
            'divisor'   => 3600000,
            'suffix'    => ' kVAh',
            'digits'    => 3,
            'detail'    => true,
            'type'      => MeasurementType::ApparentPowerPositive
        ],
        '0400_app_neg'   => ['name' => 'Apparent Power -', 'divisor' => 10, 'suffix' => ' VA', 'digits' => 1, 'detail' => true, 'type' => MeasurementType::ApparentPowerNegative],
        '0800_app_neg'   => [
            'name'      => 'Counter Apparent Power -',
            'divisor'   => 3600000,
            'suffix'    => ' kVAh',
            'digits'    => 3,
            'detail'    => true,
            'type'      => MeasurementType::ApparentPowerNegative
        ],
        '0400_fac'       => ['name' => 'Power Factor', 'divisor' => 1000, 'suffix' => '', 'digits' => 3, 'detail' => true, 'type' => MeasurementType::PowerFactor],
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

        $this->RegisterVariableString('SW_VERSION', $this->Translate('SW-Version'), '', 1000);
        $this->RegisterVariableString('SERIAL_NUMBER', $this->Translate('Serial Number'), '', 1010);
    }

    private function getLookupMap(): array
    {
        $map = [];
        $pos = 10;

        // 1. Summen-Werte (Gesamtverbrauch/-einspeisung)
        foreach (self::MEASUREMENTS as $key => $m) {
            $obisType = str_contains($key, '0800') ? '0800' : '0400';
            $id = sprintf('00%02x%s', $m['type']->value, $obisType);

            $map[$id] = ['prefix' => 'SUM', 'config' => $m, 'pos' => $pos];
            $pos += self::POSITION_STEP;
        }

        // Netzfrequenz (Spezial-ID)
        $map['000e0400'] = [
            'prefix' => 'SUM',
            'config' => ['name' => 'Network Frequency', 'divisor' => 1000, 'suffix' => ' Hz', 'digits' => 2, 'detail' => false],
            'pos'    => 150
        ];

        // 2. Einzelphasen-Werte (L1, L2, L3)
        if ($this->ReadPropertyBoolean(self::PROP_SHOW_SINGLE_PHASES)) {
            $phases = [
                'L1' => ['offset' => 20, 'pos' => 300],
                'L2' => ['offset' => 40, 'pos' => 500],
                'L3' => ['offset' => 60, 'pos' => 700]
            ];

            foreach ($phases as $prefix => $pInfo) {
                $pos = $pInfo['pos'];

                // Standard-Messwerte für die Phase
                foreach (self::MEASUREMENTS as $key => $m) {
                    $obisType = str_contains($key, '0800') ? '0800' : '0400';
                    $typeWithOffset = $m['type']->value + $pInfo['offset'];
                    $id = sprintf('00%02x%s', $typeWithOffset, $obisType);

                    $map[$id] = ['prefix' => $prefix, 'config' => $m, 'pos' => $pos];
                    $pos += self::POSITION_STEP;
                }

                // Phasen-spezifische Werte: Strom (Typ + 11), Spannung (Typ + 12)
                $map[sprintf('00%02x0400', $pInfo['offset'] + 11)] = [
                    'prefix' => $prefix,
                    'config' => ['name' => 'Power', 'divisor' => 1000, 'suffix' => ' A', 'digits' => 3, 'detail' => false],
                    'pos'    => $pos + 10
                ];
                $map[sprintf('00%02x0400', $pInfo['offset'] + 12)] = [
                    'prefix' => $prefix,
                    'config' => ['name' => 'Voltage', 'divisor' => 1000, 'suffix' => ' V', 'digits' => 2, 'detail' => false],
                    'pos'    => $pos + 20
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

        try {
            $data = json_decode($JSONString, true, 512, JSON_THROW_ON_ERROR);
            $this->processData($data['Buffer']);
        } catch (\JsonException $e) {
            $this->SendDebug('Error', 'Invalid JSON: ' . $e->getMessage(), 0);
        }
        return '';
    }

    /**
     * Verarbeitet die empfangenen RAW-Daten des SMA-Geräts.
     * Das Paket wird nach dem SMA-Net-Protokoll (Header + OBIS-Datenstrom) zerlegt.
     *
     * @param string $hraw Der Datenstrom vom Multicast-Socket (Hex oder Binär).
     *
     * @return void
     */
    private function processData(string $hraw): void
    {
        // Debugging-Ausgabe vor der Konvertierung
        if ($this->ReadPropertyBoolean(self::PROP_ENTENDED_DEBUG_INFORMATION)) {
            $this->SendDebug('RAW_IN', $hraw, 0);
        }

        // Sicherstellen, dass wir mit Binärdaten arbeiten
        $data = @hex2bin($hraw);
        if ($data === false) {
            $data = $hraw;
        }

        // Mindestlänge prüfen (SMA Header ist 28 Bytes lang)
        $totalLength = strlen($data);
        if ($totalLength < 28) {
            return;
        }

        // Die Seriennummer steht im Header (4 Bytes ab Byte 20)
        $currentSerial = (string)unpack('N', substr($data, 20, 4))[1];

        // Filter: Nur verarbeiten, wenn die Seriennummer übereinstimmt (oder keine konfiguriert ist)
        $configuredSerial = $this->ReadPropertyString(self::PROP_SERIAL_NUMBER);
        if ($configuredSerial !== '' && $configuredSerial !== $currentSerial) {
            return;
        }

        // Wenn noch keine Seriennummer konfiguriert war, setzen wir sie einmalig zur Info
        $this->SetValue('SERIAL_NUMBER', $currentSerial);

        // SMA Protokoll 6069 Check (SMA Net): Andere Protokoll-IDs werden ignoriert
        $protokollID = unpack('n', substr($data, 16, 2))[1];
        if ($protokollID !== 0x6069) {
            return;
        }

        $lookup = $this->getLookupMap();
        $offset = 28; // Datenbereich beginnt nach dem SMA Header

        // Iteration durch den Datenstrom (Tag-Length-Value Format)
        while ($offset + 4 <= $totalLength) {
            $idBin = substr($data, $offset, 4);
            $idHex = bin2hex($idBin);

            // Ende des Pakets oder ungültige ID erreicht
            if ($idHex === '00000000') {
                break;
            }

            $offset += 4;
            $valLen = ord($idBin[2]); // Das 3. Byte der OBIS-ID gibt die Länge des Wertes an

            // Sicherheitscheck, ob wir noch genug Daten im String haben
            if ($offset + $valLen > $totalLength) {
                break;
            }

            if (isset($lookup[$idHex])) {
                $entry  = $lookup[$idHex];
                $config = $entry['config'];

                // Nur speichern, wenn die Variable existiert (Detail-Check)
                if (!$config['detail'] || $this->ReadPropertyBoolean(self::PROP_SHOW_DETAILED_CHANNELS)) {
                    $ident   = $this->getIdent($entry['prefix'], $config['name']);
                    $valPart = substr($data, $offset, $valLen);

                    // Wert extrahieren: 'N' (32 bit unsigned), 'J' (64 bit unsigned)
                    $rawValue = match ($valLen) {
                        4       => unpack('N', $valPart)[1],
                        8       => unpack('J', $valPart)[1],
                        default => 0
                    };

                    $this->SetValue($ident, $rawValue / $config['divisor']);
                }
            } elseif ($idHex === '90000000') {
                // Spezialbehandlung für Software-Version (4 Bytes)
                $sw = substr($data, $offset, 4);
                $swStr = sprintf('%d.%d.%d.%s', ord($sw[0]), ord($sw[1]), ord($sw[2]), $sw[3]);
                $this->SetValue('SW_VERSION', $swStr);
            }
            $offset += $valLen;
        }
    }
    private function getIdent(string $prefix, string $name): string
    {
        // Erzeugt einen gültigen IPS-Ident aus Prefix und Namen
        $name = str_replace(['+', '-'], ['pos', 'neg'], $name);
        return $prefix . '_' . preg_replace('/[^a-z0-9_]/i', '_', $name);
    }

    private function getModifiedName(string $prefix, string $name): string
    {
        // Spezialbehandlung für die Haupt-Summenwerte (Netzbezug / Netzeinspeisung)
        if ($prefix === 'SUM') {
            $special = [
                'Real Power +'         => 'Grid Consumption',
                'Counter Real Power +' => 'Grid Consumption Counter',
                'Real Power -'         => 'Grid Feed-In',
                'Counter Real Power -' => 'Grid Feed-In Counter'
            ];

            if (isset($special[$name])) {
                $mainTitle = $this->Translate($special[$name]);
                $subTitle  = $this->Translate(str_replace([' +', ' -'], '', $name));
                $sign      = str_contains($name, '+') ? '+' : '-';

                return sprintf('%s (%s(%s) gesamt)', $mainTitle, $subTitle, $sign);
            }
        }

        $suffix = match (true) {
            str_ends_with($name, ' +') => ' +',
            str_ends_with($name, ' -') => ' -',
            default => ''
        };

        $cleaned = $suffix === '' ? $name : substr($name, 0, -strlen($suffix));
        $transName = $this->Translate($cleaned);
        $transPrefix = $this->Translate($prefix);

        if ($prefix === 'SUM') {
            return $transName . $suffix;
        }

        return $transPrefix . ' ' . $transName . $suffix;
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