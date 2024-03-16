<?php

declare(strict_types=1);
class SMAHomeManagerDevice extends IPSModule
{
    private const MODID_MULTICAST_SOCKET      = '{BAB408E0-0A0F-48C3-B14E-9FB2FA81F66A}';
    private const PROP_SHOW_DETAILED_CHANNELS = 'ShowDetailedChannels';
    private const PROP_SHOW_SINGLE_PHASES     = 'ShowSinglePhases';

    private const PROFILE_ELECTRICITY_KWH = 'SMAHM.Electricity.kWh';

    // OBIS Parameter

    //Summen
    private const LIST_SUM = [
        '00010400' => ['OBIS' => '0140', 'divisor' => 10, 'profile' => '~Watt', 'name' => 'Real Power +', 'detail' => false],
        '00010800' => ['OBIS'    => '0180',
                       'divisor' => 3600000,
                       'profile' => self::PROFILE_ELECTRICITY_KWH,
                       'name'    => 'Counter Real Power +',
                       'detail'  => false
        ],
        '00020400' => ['OBIS' => '0240', 'divisor' => 10, 'profile' => '~Watt', 'name' => 'Real Power -', 'detail' => false],
        '00020800' => ['OBIS'    => '0280',
                       'divisor' => 3600000,
                       'profile' => self::PROFILE_ELECTRICITY_KWH,
                       'name'    => 'Counter Real Power -',
                       'detail'  => false
        ],
        '00030400' => ['OBIS' => '0340', 'divisor' => 10, 'profile' => '~Watt', 'name' => 'Reactive Power +', 'detail' => true],
        '00030800' => [
            'OBIS'    => '0380',
            'divisor' => 3600000,
            'profile' => self::PROFILE_ELECTRICITY_KWH,
            'name'    => 'Counter Reactive Power +',
            'detail'  => true
        ],
        '00040400' => ['OBIS' => '0440', 'divisor' => 10, 'profile' => '~Watt', 'name' => 'Reactive Power -', 'detail' => true],
        '00040800' => [
            'OBIS'    => '0480',
            'divisor' => 3600000,
            'profile' => self::PROFILE_ELECTRICITY_KWH,
            'name'    => 'Counter Reactive Power -',
            'detail'  => true
        ],
        '00090400' => ['OBIS' => '0940', 'divisor' => 10, 'profile' => '~Watt', 'name' => 'Apparent Power +', 'detail' => true],
        '00090800' => [
            'OBIS'    => '0980',
            'divisor' => 3600000,
            'profile' => self::PROFILE_ELECTRICITY_KWH,
            'name'    => 'Counter Apparent Power +',
            'detail'  => true
        ],
        '000a0400' => ['OBIS' => '1040', 'divisor' => 10, 'profile' => '~Watt', 'name' => 'Apparent Power -', 'detail' => true],
        '000a0800' => [
            'OBIS'    => '1080',
            'divisor' => 3600000,
            'profile' => self::PROFILE_ELECTRICITY_KWH,
            'name'    => 'Counter Apparent Power -',
            'detail'  => true
        ],
        '000d0400' => ['OBIS' => '1340', 'divisor' => 1000, 'profile' => '', 'name' => 'Power Factor', 'detail' => true],
        '000e0400' => ['OBIS' => '1440', 'divisor' => 1000, 'profile' => '~Hertz.50', 'name' => 'Network Frequency', 'detail' => true]
    ];

    private const LIST_L1 = [ //Phase 1
                              '00150400' => ['OBIS' => '2140', 'divisor' => 10, 'profile' => '~Watt', 'name' => 'Real Power +', 'detail' => false],
                              '00150800' => [
                                  'OBIS'    => '2180',
                                  'divisor' => 3600000,
                                  'profile' => self::PROFILE_ELECTRICITY_KWH,
                                  'name'    => 'Counter Real Power +',
                                  'detail'  => false
                              ],
                              '00160400' => ['OBIS' => '2240', 'divisor' => 10, 'profile' => '~Watt', 'name' => 'Real Power -', 'detail' => false],
                              '00160800' => [
                                  'OBIS'    => '2280',
                                  'divisor' => 3600000,
                                  'profile' => self::PROFILE_ELECTRICITY_KWH,
                                  'name'    => 'Counter Real Power -',
                                  'detail'  => false
                              ],
                              '00170400' => ['OBIS' => '2340', 'divisor' => 10, 'profile' => '~Watt', 'name' => 'Reactive Power +', 'detail' => true],
                              '00170800' => [
                                  'OBIS'    => '2380',
                                  'divisor' => 3600000,
                                  'profile' => self::PROFILE_ELECTRICITY_KWH,
                                  'name'    => 'Counter Reactive Power +',
                                  'detail'  => true
                              ],
                              '00180400' => ['OBIS' => '2440', 'divisor' => 10, 'profile' => '~Watt', 'name' => 'Reactive Power -', 'detail' => true],
                              '00180800' => [
                                  'OBIS'    => '2480',
                                  'divisor' => 3600000,
                                  'profile' => self::PROFILE_ELECTRICITY_KWH,
                                  'name'    => 'Counter Reactive Power -',
                                  'detail'  => true
                              ],
                              '001d0400' => ['OBIS' => '2940', 'divisor' => 10, 'profile' => '~Watt', 'name' => 'Apparent Power +', 'detail' => true],
                              '001d0800' => [
                                  'OBIS'    => '2980',
                                  'divisor' => 3600000,
                                  'profile' => self::PROFILE_ELECTRICITY_KWH,
                                  'name'    => 'Counter Apparent Power +',
                                  'detail'  => true
                              ],
                              '001e0400' => ['OBIS' => '3040', 'divisor' => 10, 'profile' => '~Watt', 'name' => 'Apparent Power -', 'detail' => true],
                              '001e0800' => [
                                  'OBIS'    => '3080',
                                  'divisor' => 3600000,
                                  'profile' => self::PROFILE_ELECTRICITY_KWH,
                                  'name'    => 'Counter Apparent Power -',
                                  'detail'  => true
                              ],
                              '001f0400' => ['OBIS' => '3140', 'divisor' => 1000, 'profile' => '~Ampere', 'name' => 'Power', 'detail' => true],
                              '00200400' => ['OBIS' => '3240', 'divisor' => 1000, 'profile' => '~Volt.230', 'name' => 'Voltage', 'detail' => true],
                              '00210400' => ['OBIS' => '3340', 'divisor' => 1000, 'profile' => '', 'name' => 'Power Factor', 'detail' => true]
    ];

    private const LIST_L2 = [ //Phase 2
                              '00290400' => ['OBIS' => '4140', 'divisor' => 10, 'profile' => '~Watt', 'name' => 'Real Power +', 'detail' => false],
                              '00290800' => [
                                  'OBIS'    => '4180',
                                  'divisor' => 3600000,
                                  'profile' => self::PROFILE_ELECTRICITY_KWH,
                                  'name'    => 'Counter Real Power +',
                                  'detail'  => false
                              ],
                              '002a0400' => ['OBIS' => '4240', 'divisor' => 10, 'profile' => '~Watt', 'name' => 'Real Power -', 'detail' => false],
                              '002a0800' => [
                                  'OBIS'    => '4280',
                                  'divisor' => 3600000,
                                  'profile' => self::PROFILE_ELECTRICITY_KWH,
                                  'name'    => 'Counter Real Power -',
                                  'detail'  => false
                              ],
                              '002b0400' => ['OBIS' => '4340', 'divisor' => 10, 'profile' => '~Watt', 'name' => 'Reactive Power +', 'detail' => true],
                              '002b0800' => [
                                  'OBIS'    => '4380',
                                  'divisor' => 3600000,
                                  'profile' => self::PROFILE_ELECTRICITY_KWH,
                                  'name'    => 'Counter Reactive Power +',
                                  'detail'  => true
                              ],
                              '002c0400' => ['OBIS' => '4440', 'divisor' => 10, 'profile' => '~Watt', 'name' => 'Reactive Power -', 'detail' => true],
                              '002c0800' => [
                                  'OBIS'    => '4480',
                                  'divisor' => 3600000,
                                  'profile' => self::PROFILE_ELECTRICITY_KWH,
                                  'name'    => 'Counter Reactive Power -',
                                  'detail'  => true
                              ],
                              '00310400' => ['OBIS' => '4940', 'divisor' => 10, 'profile' => '~Watt', 'name' => 'Apparent Power +', 'detail' => true],
                              '00310800' => [
                                  'OBIS'    => '4980',
                                  'divisor' => 3600000,
                                  'profile' => self::PROFILE_ELECTRICITY_KWH,
                                  'name'    => 'Counter Apparent Power +',
                                  'detail'  => true
                              ],
                              '00320400' => ['OBIS' => '5040', 'divisor' => 10, 'profile' => '~Watt', 'name' => 'Apparent Power -', 'detail' => true],
                              '00320800' => [
                                  'OBIS'    => '5080',
                                  'divisor' => 3600000,
                                  'profile' => '~Electricity.Wh',
                                  'name'    => self::PROFILE_ELECTRICITY_KWH,
                                  'detail'  => true
                              ],
                              '00330400' => ['OBIS' => '5140', 'divisor' => 1000, 'profile' => '~Ampere', 'name' => 'Power', 'detail' => true],
                              '00340400' => ['OBIS' => '5240', 'divisor' => 1000, 'profile' => '~Volt.230', 'name' => 'Voltage', 'detail' => true],
                              '00350400' => ['OBIS' => '5340', 'divisor' => 1000, 'profile' => '', 'name' => 'Power Faktor', 'detail' => true]
    ];

    private const LIST_L3 = [ //Phase 3
                              '003d0400' => ['OBIS' => '6140', 'divisor' => 10, 'profile' => '~Watt', 'name' => 'Real Power +', 'detail' => false],
                              '003d0800' => [
                                  'OBIS'    => '6180',
                                  'divisor' => 3600000,
                                  'profile' => self::PROFILE_ELECTRICITY_KWH,
                                  'name'    => 'Counter Real Power +',
                                  'detail'  => false
                              ],
                              '003e0400' => ['OBIS' => '6240', 'divisor' => 10, 'profile' => '~Watt', 'name' => 'Real Power -', 'detail' => false],
                              '003e0800' => [
                                  'OBIS'    => '6280',
                                  'divisor' => 3600000,
                                  'profile' => self::PROFILE_ELECTRICITY_KWH,
                                  'name'    => 'Counter Real Power -',
                                  'detail'  => false
                              ],
                              '003f0400' => ['OBIS' => '6340', 'divisor' => 10, 'profile' => '~Watt', 'name' => 'Reactive Power +', 'detail' => true],
                              '003f0800' => [
                                  'OBIS'    => '6380',
                                  'divisor' => 3600000,
                                  'profile' => self::PROFILE_ELECTRICITY_KWH,
                                  'name'    => 'Counter Reactive Power +',
                                  'detail'  => true
                              ],
                              '00400400' => ['OBIS' => '6440', 'divisor' => 10, 'profile' => '~Watt', 'name' => 'Reactive Power -', 'detail' => true],
                              '00400800' => [
                                  'OBIS'    => '6480',
                                  'divisor' => 3600000,
                                  'profile' => self::PROFILE_ELECTRICITY_KWH,
                                  'name'    => 'Counter Reactive Power -',
                                  'detail'  => true
                              ],
                              '00450400' => ['OBIS' => '6940', 'divisor' => 10, 'profile' => '~Watt', 'name' => 'Apparent Power +', 'detail' => true],
                              '00450800' => [
                                  'OBIS'    => '6980',
                                  'divisor' => 3600000,
                                  'profile' => self::PROFILE_ELECTRICITY_KWH,
                                  'name'    => 'Counter Apparent Power +',
                                  'detail'  => true
                              ],
                              '00460400' => ['OBIS' => '7040', 'divisor' => 10, 'profile' => '~Watt', 'name' => 'Apparent Power -', 'detail' => true],
                              '00460800' => [
                                  'OBIS'    => '7080',
                                  'divisor' => 3600000,
                                  'profile' => self::PROFILE_ELECTRICITY_KWH,
                                  'name'    => 'Counter Apparent Power -',
                                  'detail'  => true
                              ],
                              '00470400' => ['OBIS' => '7140', 'divisor' => 1000, 'profile' => '~Ampere', 'name' => 'Power', 'detail' => true],
                              '00480400' => ['OBIS' => '7240', 'divisor' => 1000, 'profile' => '~Volt.230', 'name' => 'Voltage', 'detail' => true],
                              '00490400' => ['OBIS' => '7340', 'divisor' => 1000, 'profile' => '', 'name' => 'Power Faktor', 'detail' => true]
    ];

    private const POSITION_STEP = 10;

    public function Create(): void
    {
        //Never delete this line!
        parent::Create();

        $this->RegisterPropertyBoolean(self::PROP_SHOW_DETAILED_CHANNELS, false);
        $this->RegisterPropertyBoolean(self::PROP_SHOW_SINGLE_PHASES, false);


        $this->RequireParent(self::MODID_MULTICAST_SOCKET);
    }

    private function CreateProfiles(): void
    {
        if (!IPS_VariableProfileExists(self::PROFILE_ELECTRICITY_KWH)) {
            IPS_CreateVariableProfile(self::PROFILE_ELECTRICITY_KWH, VARIABLETYPE_FLOAT);
            IPS_SetVariableProfileText(self::PROFILE_ELECTRICITY_KWH, '', ' kWh');
        }
    }

    private function RegisterVariables(): void
    {
        $this->registerChannelVariablesFromList('SUM', self::LIST_SUM, 10);

        if ($this->ReadPropertyBoolean(self::PROP_SHOW_SINGLE_PHASES)) {
            $this->registerChannelVariablesFromList('L1', self::LIST_L1, 300);
            $this->registerChannelVariablesFromList('L2', self::LIST_L2, 500);
            $this->registerChannelVariablesFromList('L3', self::LIST_L3, 700);
        }
    }

    private function registerChannelVariablesFromList(string $prefix, array $list, int $initialPosition): void
    {
        $position = $initialPosition;
        foreach ($list as $channel) {
            $this->RegisterChannelVariable($prefix, $channel, $position);
            $position += self::POSITION_STEP;
        }
    }

    private function RegisterChannelVariable(string $prefix, array $channel, int $position): void
    {
        if (!$channel['detail'] || $this->ReadPropertyBoolean(self::PROP_SHOW_DETAILED_CHANNELS)) {
            $ident = $this->getIdent($prefix, $channel['name']);
            $this->RegisterVariableFloat($ident, $this->getModifiedName($prefix, $channel['name']), $channel['profile'], $position);
        }
    }

    private function getModifiedName(string $prefix, string $name): string
    {
        $suffix        = $this->getSuffix($name);
        $cleanedString = str_replace([' +', ' -'], '', $name);

        $translatedString = $this->Translate($cleanedString);

        if ($prefix !== 'SUM') {
            return $prefix . ' ' . $translatedString . $suffix;
        }

        return $translatedString . $suffix;
    }

    private function getSuffix(string $name): string
    {
        if (str_contains($name, ' +')) {
            return ' +';
        }

        if (str_contains($name, ' -')) {
            return ' -';
        }

        return '';
    }

    private function getIdent(string $prefix, string $name): string
    {
        $name = str_replace(['+', '-'], ['pos', 'neg'], $name);
        return $prefix . '_' . preg_replace('/[^a-z0-9_]/i', '_', $name); //alles bis auf a-z, A-Z, 0-9 und '_' durch '_' ersetzen
    }

    public function ApplyChanges(): void
    {
        $this->RegisterVariables();
        $this->CreateProfiles();

        $this->SetStatus(IS_ACTIVE);

        //Never delete this line!
        parent::ApplyChanges();
    }

    public function ReceiveData($JSONString): void
    {
        $data = json_decode($JSONString, true, 512, JSON_THROW_ON_ERROR);
        /*
        $this->SendDebug(
            sprintf('%s (%s:%s, %s)', __FUNCTION__, $data['ClientIP'], $data['ClientPort'], $data['DataID']),
            utf8_decode($data['Buffer']),
            0
        );
        */
        $this->processData($data['Buffer']);
    }

    private function processData(string $buffer): void
    {
        $hraw = bin2hex(utf8_decode($buffer));
        $this->SendDebug(sprintf('%s (%s)', __FUNCTION__, 'hraw'), $hraw, 0);

        //Erkennungsstring
        $offset = 0;
        $len    = 4;
        //$this->SendDebug(sprintf('%s (%s)', __FUNCTION__, 'Erkennungsstring'), hex2bin(substr($hraw, $offset * 2, $len * 2)), 0);

        //Datenlänge
        $offset += $len;
        $len    = 4;
        //$this->SendDebug(sprintf('%s (%s)', __FUNCTION__, 'Datenlänge'), substr($hraw, $offset * 2, $len * 2), 0);

        //gruppe
        $offset += $len;
        $len    = 2;
        //$this->SendDebug(sprintf('%s (%s)', __FUNCTION__, 'Gruppe'), substr($hraw, $offset * 2, $len * 2), 0);

        //ProtokollID
        $offset      = 16; //müsste sich eigentlich aus dem Protokoll ergeben
        $len         = 2;
        $protokollID = substr($hraw, $offset * 2, $len * 2);
        $this->SendDebug(sprintf('%s (%s)', __FUNCTION__, 'ProtokollID'), $protokollID, 0);
        if ($protokollID === '6065') {
            $this->SendDebug(sprintf('%s (%s)', __FUNCTION__, 'ProtokollID'), '- ignored -', 0);
            return;
        }

        //zaehlerkennung
        $offset += $len;
        $len    = 6;
        //$this->SendDebug(sprintf('%s (%s)', __FUNCTION__, 'Zählerkennung'), substr($hraw, $offset * 2, $len * 2), 0);

        //Messzeitpunkt
        $offset += $len;
        $len    = 4;
        //$this->SendDebug(sprintf('%s (%s)', __FUNCTION__, 'Messzeitpunkt'), base_convert(substr($hraw, $offset * 2, $len * 2), 16, 10), 0);

        $offset   += $len;
        $finished = false;

        while (!$finished) {
            //obis Id
            $len = 4;
            $id  = strtolower(substr($hraw, $offset * 2, $len * 2));
            //$this->SendDebug(sprintf('%s (%s)', __FUNCTION__, 'id'), $id,0);

            //echo $id . PHP_EOL;
            if ($id === '00000000') {
                $finished = true;
                continue;
            }
            $offset += $len;

            //obis Messwert
            $len = (int)substr($id, 2 * 2, 2); //die Länge entspricht der Messart (Byte 2)

            if (isset(self::LIST_SUM[$id])) {
                $this->setValueFromHexAndList($id, $hraw, $offset, $len, 'SUM', self::LIST_SUM);
            } elseif (isset(self::LIST_L1[$id])) {
                $this->setValueFromHexAndList($id, $hraw, $offset, $len, 'L1', self::LIST_L1);
            } elseif (isset(self::LIST_L2[$id])) {
                $this->setValueFromHexAndList($id, $hraw, $offset, $len, 'L2', self::LIST_L2);
            } elseif (isset(self::LIST_L3[$id])) {
                $this->setValueFromHexAndList($id, $hraw, $offset, $len, 'L3', self::LIST_L3);
            } elseif ($id === '90000000') {
                $len       = 4;
                $swVersion = substr($hraw, $offset * 2, $len * 2);

                $this->SendDebug(
                    sprintf('%s (%s)', __FUNCTION__, 'SW-Version'),
                    sprintf(
                        '%s.%s.%s.%s',
                        hexdec(substr($swVersion, 0, 2)),
                        hexdec(substr($swVersion, 2, 2)),
                        hexdec(substr($swVersion, 4, 2)),
                        hexdec(substr($swVersion, 4, 2))
                    ),
                    0
                );
            } else {
                trigger_error("$id unbekannt");
                $finished = true;
            }
            $offset += $len;
        }
    }

    private function getSubstringFromHex(string $hraw, int $offset, int $len): string
    {
        return substr($hraw, $offset * 2, $len * 2);
    }

    private function setValueFromHexAndList(string $id, string $hraw, int $offset, int $len, string $prefix, array $list): void
    {
        $ident = $this->getIdent($prefix, $list[$id]['name']);
        /*
        $this->SendDebug(
            sprintf('%s (%s)', __FUNCTION__, $id),
            sprintf(
                '%s: %s, %s, %s',
                $prefix,
                (int) $this->ReadPropertyBoolean(self::PROP_SHOW_SINGLE_PHASES),
                (int) $this->ReadPropertyBoolean(self::PROP_SHOW_DETAILED_CHANNELS),
                (int) $list[$id]['detail']
            ),
            0
        );
        */

        if (($prefix === 'SUM' || $this->ReadPropertyBoolean(self::PROP_SHOW_SINGLE_PHASES))
            && ($this->ReadPropertyBoolean(self::PROP_SHOW_DETAILED_CHANNELS)
                || !$list[$id]['detail'])) {
            $this->SetValue(
                $ident,
                base_convert($this->getSubstringFromHex($hraw, $offset, $len), 16, 10) / $list[$id]['divisor']
            );
        }
    }
}