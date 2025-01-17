<?php

declare(strict_types=1);
class SMAHomeManagerDevice extends IPSModuleStrict
{
    private const MODID_MULTICAST_SOCKET          = '{BAB408E0-0A0F-48C3-B14E-9FB2FA81F66A}';
    private const PROP_SHOW_DETAILED_CHANNELS     = 'ShowDetailedChannels';
    private const PROP_SHOW_SINGLE_PHASES         = 'ShowSinglePhases';
    private const PROP_EXTENDED_UPDATE_INTERVAL   = 'ExtendedUpdateInterval';
    private const PROP_ENTENDED_DEBUG_INFORMATION = 'ExtendedDebugInformation';

    private const PROFILE_ELECTRICITY_KWH   = 'SMAHM.Electricity.kWh';
    private const PROFILE_ELECTRICITY_VA    = 'SMAHM.Electricity.VA';
    private const PROFILE_ELECTRICITY_KVAH  = 'SMAHM.Electricity.kVAh';
    private const PROFILE_ELECTRICITY_VAR   = 'SMAHM.Electricity.var';
    private const PROFILE_ELECTRICITY_KVARH = 'SMAHM.Electricity.kvarh';

    // OBIS Parameter

    //Summen
    private const LIST_SUM = [
        '00010400' => ['OBIS' => '0140', 'divisor' => 10, 'profile' => '~Watt', 'name' => 'Real Power +', 'detail' => false],
        '00010800' => [
            'OBIS'    => '0180',
            'divisor' => 3600000,
            'profile' => self::PROFILE_ELECTRICITY_KWH,
            'name'    => 'Counter Real Power +',
            'detail'  => false
        ],
        '00020400' => ['OBIS' => '0240', 'divisor' => 10, 'profile' => '~Watt', 'name' => 'Real Power -', 'detail' => false],
        '00020800' => [
            'OBIS'    => '0280',
            'divisor' => 3600000,
            'profile' => self::PROFILE_ELECTRICITY_KWH,
            'name'    => 'Counter Real Power -',
            'detail'  => false
        ],
        '00030400' => ['OBIS' => '0340', 'divisor' => 10, 'profile' => self::PROFILE_ELECTRICITY_VAR, 'name' => 'Reactive Power +', 'detail' => true],
        '00030800' => [
            'OBIS'    => '0380',
            'divisor' => 3600000,
            'profile' => self::PROFILE_ELECTRICITY_KVARH,
            'name'    => 'Counter Reactive Power +',
            'detail'  => true
        ],
        '00040400' => ['OBIS' => '0440', 'divisor' => 10, 'profile' => self::PROFILE_ELECTRICITY_VAR, 'name' => 'Reactive Power -', 'detail' => true],
        '00040800' => [
            'OBIS'    => '0480',
            'divisor' => 3600000,
            'profile' => self::PROFILE_ELECTRICITY_KVARH,
            'name'    => 'Counter Reactive Power -',
            'detail'  => true
        ],
        '00090400' => ['OBIS' => '0940', 'divisor' => 10, 'profile' => self::PROFILE_ELECTRICITY_VA, 'name' => 'Apparent Power +', 'detail' => true],
        '00090800' => [
            'OBIS'    => '0980',
            'divisor' => 3600000,
            'profile' => self::PROFILE_ELECTRICITY_KVAH,
            'name'    => 'Counter Apparent Power +',
            'detail'  => true
        ],
        '000a0400' => ['OBIS' => '1040', 'divisor' => 10, 'profile' => self::PROFILE_ELECTRICITY_VA, 'name' => 'Apparent Power -', 'detail' => true],
        '000a0800' => [
            'OBIS'    => '1080',
            'divisor' => 3600000,
            'profile' => self::PROFILE_ELECTRICITY_KVAH,
            'name'    => 'Counter Apparent Power -',
            'detail'  => true
        ],
        '000d0400' => ['OBIS' => '1340', 'divisor' => 1000, 'profile' => '', 'name' => 'Power Factor', 'detail' => true],
        '000e0400' => ['OBIS' => '1440', 'divisor' => 1000, 'profile' => '~Hertz.50', 'name' => 'Network Frequency', 'detail' => false]
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
                              '00170400' => [
                                  'OBIS'    => '2340',
                                  'divisor' => 10,
                                  'profile' => self::PROFILE_ELECTRICITY_VAR,
                                  'name'    => 'Reactive Power +',
                                  'detail'  => true
                              ],
                              '00170800' => [
                                  'OBIS'    => '2380',
                                  'divisor' => 3600000,
                                  'profile' => self::PROFILE_ELECTRICITY_KVARH,
                                  'name'    => 'Counter Reactive Power +',
                                  'detail'  => true
                              ],
                              '00180400' => [
                                  'OBIS'    => '2440',
                                  'divisor' => 10,
                                  'profile' => self::PROFILE_ELECTRICITY_VAR,
                                  'name'    => 'Reactive Power -',
                                  'detail'  => true
                              ],
                              '00180800' => [
                                  'OBIS'    => '2480',
                                  'divisor' => 3600000,
                                  'profile' => self::PROFILE_ELECTRICITY_KVARH,
                                  'name'    => 'Counter Reactive Power -',
                                  'detail'  => true
                              ],
                              '001d0400' => [
                                  'OBIS'    => '2940',
                                  'divisor' => 10,
                                  'profile' => self::PROFILE_ELECTRICITY_VA,
                                  'name'    => 'Apparent Power +',
                                  'detail'  => true
                              ],
                              '001d0800' => [
                                  'OBIS'    => '2980',
                                  'divisor' => 3600000,
                                  'profile' => self::PROFILE_ELECTRICITY_KVAH,
                                  'name'    => 'Counter Apparent Power +',
                                  'detail'  => true
                              ],
                              '001e0400' => [
                                  'OBIS'    => '3040',
                                  'divisor' => 10,
                                  'profile' => self::PROFILE_ELECTRICITY_VA,
                                  'name'    => 'Apparent Power -',
                                  'detail'  => true
                              ],
                              '001e0800' => [
                                  'OBIS'    => '3080',
                                  'divisor' => 3600000,
                                  'profile' => self::PROFILE_ELECTRICITY_KVAH,
                                  'name'    => 'Counter Apparent Power -',
                                  'detail'  => true
                              ],
                              '001f0400' => ['OBIS' => '3140', 'divisor' => 1000, 'profile' => '~Ampere', 'name' => 'Power', 'detail' => false],
                              '00200400' => ['OBIS' => '3240', 'divisor' => 1000, 'profile' => '~Volt.230', 'name' => 'Voltage', 'detail' => false],
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
                              '002b0400' => [
                                  'OBIS'    => '4340',
                                  'divisor' => 10,
                                  'profile' => self::PROFILE_ELECTRICITY_VAR,
                                  'name'    => 'Reactive Power +',
                                  'detail'  => true
                              ],
                              '002b0800' => [
                                  'OBIS'    => '4380',
                                  'divisor' => 3600000,
                                  'profile' => self::PROFILE_ELECTRICITY_KVARH,
                                  'name'    => 'Counter Reactive Power +',
                                  'detail'  => true
                              ],
                              '002c0400' => [
                                  'OBIS'    => '4440',
                                  'divisor' => 10,
                                  'profile' => self::PROFILE_ELECTRICITY_VAR,
                                  'name'    => 'Reactive Power -',
                                  'detail'  => true
                              ],
                              '002c0800' => [
                                  'OBIS'    => '4480',
                                  'divisor' => 3600000,
                                  'profile' => self::PROFILE_ELECTRICITY_KVARH,
                                  'name'    => 'Counter Reactive Power -',
                                  'detail'  => true
                              ],
                              '00310400' => [
                                  'OBIS'    => '4940',
                                  'divisor' => 10,
                                  'profile' => self::PROFILE_ELECTRICITY_VA,
                                  'name'    => 'Apparent Power +',
                                  'detail'  => true
                              ],
                              '00310800' => [
                                  'OBIS'    => '4980',
                                  'divisor' => 3600000,
                                  'profile' => self::PROFILE_ELECTRICITY_KVAH,
                                  'name'    => 'Counter Apparent Power +',
                                  'detail'  => true
                              ],
                              '00320400' => [
                                  'OBIS'    => '5040',
                                  'divisor' => 10,
                                  'profile' => self::PROFILE_ELECTRICITY_VA,
                                  'name'    => 'Apparent Power -',
                                  'detail'  => true
                              ],
                              '00320800' => [
                                  'OBIS'    => '5080',
                                  'divisor' => 3600000,
                                  'profile' => self::PROFILE_ELECTRICITY_KVAH,
                                  'name'    => 'Counter Apparent Power -',
                                  'detail'  => true
                              ],
                              '00330400' => ['OBIS' => '5140', 'divisor' => 1000, 'profile' => '~Ampere', 'name' => 'Power', 'detail' => false],
                              '00340400' => ['OBIS' => '5240', 'divisor' => 1000, 'profile' => '~Volt.230', 'name' => 'Voltage', 'detail' => false],
                              '00350400' => ['OBIS' => '5340', 'divisor' => 1000, 'profile' => '', 'name' => 'Power Factor', 'detail' => true]
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
                              '003f0400' => [
                                  'OBIS'    => '6340',
                                  'divisor' => 10,
                                  'profile' => self::PROFILE_ELECTRICITY_VAR,
                                  'name'    => 'Reactive Power +',
                                  'detail'  => true
                              ],
                              '003f0800' => [
                                  'OBIS'    => '6380',
                                  'divisor' => 3600000,
                                  'profile' => self::PROFILE_ELECTRICITY_KVARH,
                                  'name'    => 'Counter Reactive Power +',
                                  'detail'  => true
                              ],
                              '00400400' => [
                                  'OBIS'    => '6440',
                                  'divisor' => 10,
                                  'profile' => self::PROFILE_ELECTRICITY_VAR,
                                  'name'    => 'Reactive Power -',
                                  'detail'  => true
                              ],
                              '00400800' => [
                                  'OBIS'    => '6480',
                                  'divisor' => 3600000,
                                  'profile' => self::PROFILE_ELECTRICITY_KVARH,
                                  'name'    => 'Counter Reactive Power -',
                                  'detail'  => true
                              ],
                              '00450400' => [
                                  'OBIS'    => '6940',
                                  'divisor' => 10,
                                  'profile' => self::PROFILE_ELECTRICITY_VA,
                                  'name'    => 'Apparent Power +',
                                  'detail'  => true
                              ],
                              '00450800' => [
                                  'OBIS'    => '6980',
                                  'divisor' => 3600000,
                                  'profile' => self::PROFILE_ELECTRICITY_KVAH,
                                  'name'    => 'Counter Apparent Power +',
                                  'detail'  => true
                              ],
                              '00460400' => [
                                  'OBIS'    => '7040',
                                  'divisor' => 10,
                                  'profile' => self::PROFILE_ELECTRICITY_VA,
                                  'name'    => 'Apparent Power -',
                                  'detail'  => true
                              ],
                              '00460800' => [
                                  'OBIS'    => '7080',
                                  'divisor' => 3600000,
                                  'profile' => self::PROFILE_ELECTRICITY_KVAH,
                                  'name'    => 'Counter Apparent Power -',
                                  'detail'  => true
                              ],
                              '00470400' => ['OBIS' => '7140', 'divisor' => 1000, 'profile' => '~Ampere', 'name' => 'Power', 'detail' => false],
                              '00480400' => ['OBIS' => '7240', 'divisor' => 1000, 'profile' => '~Volt.230', 'name' => 'Voltage', 'detail' => false],
                              '00490400' => ['OBIS' => '7340', 'divisor' => 1000, 'profile' => '', 'name' => 'Power Factor', 'detail' => true]
    ];

    private const POSITION_STEP = 10;

    public function Create(): void
    {
        //Never delete this line!
        parent::Create();

        $this->RegisterPropertyBoolean(self::PROP_SHOW_DETAILED_CHANNELS, false);
        $this->RegisterPropertyBoolean(self::PROP_SHOW_SINGLE_PHASES, false);
        $this->RegisterPropertyInteger(self::PROP_EXTENDED_UPDATE_INTERVAL, 0);
        $this->RegisterPropertyBoolean(self::PROP_ENTENDED_DEBUG_INFORMATION, false);

        $this->RequireParent(self::MODID_MULTICAST_SOCKET);
    }

    private function CreateProfiles(): void
    {
        if (!IPS_VariableProfileExists(self::PROFILE_ELECTRICITY_KWH)) {
            IPS_CreateVariableProfile(self::PROFILE_ELECTRICITY_KWH, VARIABLETYPE_FLOAT);
            IPS_SetVariableProfileText(self::PROFILE_ELECTRICITY_KWH, '', ' kWh');
        }
        if (!IPS_VariableProfileExists(self::PROFILE_ELECTRICITY_VAR)) {
            IPS_CreateVariableProfile(self::PROFILE_ELECTRICITY_VAR, VARIABLETYPE_FLOAT);
            IPS_SetVariableProfileText(self::PROFILE_ELECTRICITY_VAR, '', ' var');
            IPS_SetVariableProfileDigits(self::PROFILE_ELECTRICITY_VAR, 1);
        }
        if (!IPS_VariableProfileExists(self::PROFILE_ELECTRICITY_KVARH)) {
            IPS_CreateVariableProfile(self::PROFILE_ELECTRICITY_KVARH, VARIABLETYPE_FLOAT);
            IPS_SetVariableProfileText(self::PROFILE_ELECTRICITY_KVARH, '', ' kvarh');
        }
        if (!IPS_VariableProfileExists(self::PROFILE_ELECTRICITY_VA)) {
            IPS_CreateVariableProfile(self::PROFILE_ELECTRICITY_VA, VARIABLETYPE_FLOAT);
            IPS_SetVariableProfileText(self::PROFILE_ELECTRICITY_VA, '', ' VA');
            IPS_SetVariableProfileDigits(self::PROFILE_ELECTRICITY_VA, 1);
        }
        if (!IPS_VariableProfileExists(self::PROFILE_ELECTRICITY_KVAH)) {
            IPS_CreateVariableProfile(self::PROFILE_ELECTRICITY_KVAH, VARIABLETYPE_FLOAT);
            IPS_SetVariableProfileText(self::PROFILE_ELECTRICITY_KVAH, '', ' kVAh');
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
        $this->RegisterVariableString('SW_VERSION', $this->Translate('SW-Version'), '', 1000);
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
        $this->CreateProfiles();
        $this->RegisterVariables();

        $this->SetStatus(IS_ACTIVE);

        //Never delete this line!
        parent::ApplyChanges();
    }

    public function ReceiveData($JSONString): string
    {
        $data = json_decode($JSONString, true, 512, JSON_THROW_ON_ERROR);
        if ($this->ReadPropertyBoolean(self::PROP_ENTENDED_DEBUG_INFORMATION)) {
            $this->SendDebug(
                sprintf('%s (%s:%s, %s)', __FUNCTION__, $data['ClientIP'], $data['ClientPort'], $data['DataID']),
                $data['Buffer'],
                0
            );
        }
        $this->processData($data['Buffer']);
        return '';
    }

    public function GetConfigurationForParent(): string
    {
        return json_encode([
                               'Port'               => 9522,
                               'BindPort'           => 9522,
                               'MulticastIP'        => '239.12.255.254',
                               'EnableBroadcast'    => false,
                               'EnableReuseAddress' => false,
                               'EnableLoopback'     => false
                           ],
                           JSON_THROW_ON_ERROR);
    }

    private function processData(string $hraw): void
    {
//        $hraw = '534d4100000402a000000001024c0010606901f5b3b8f0ccb40a28ee00010400000000000001080000000001398f2c280002040000015923000208000000000c6bad6f98000304000000042700030800000000002f2071a8000404000000000000040800000000038a83f1c00009040000000000000908000000000207f7fa40000a04000001592a000a08000000000e32bfa9b0000d0400000003e8000e04000000c36f00150400000000000015080000000002160cf0180016040000006c3d00160800000000041f0b0c98001704000000029d001708000000000014d34c08001804000000000000180800000000019b47ccb8001d040000000000001d08000000000260c687b8001e040000006c45001e0800000000047bb9d668001f040000002c79002004000003b8be00210400000003e80029040000000000002908000000000097f628d0002a0400000077ea002a0800000000053ba6b680002b040000000312002b08000000000044f85090002c040000000000002c080000000000b3e6b70800310400000000000031080000000001087d1bb800320400000077f400320800000000054b48eab8003304000000313f003404000003b7e100350400000003e8003d040000000000003d080000000000be7e2408003e0400000074fd003e08000000000543edc2e8003f040000000000003f08000000000009a76148004004000000018800400800000000016fa7fa38004504000000000000450800000000016556b1a000460400000074ff004608000000000559c793a0004704000000306a004804000003b10900490400000003e890000000020e0d5200000000';
//        $hraw = '534d4100 0004 02a0 0000 0001 024c 0010 6069 01f5b3b8f0cc b40a28ee 0001 0400000000000001080000000001398f2c280002040000015923000208000000000c6bad6f98000304000000042700030800000000002f2071a8000404000000000000040800000000038a83f1c00009040000000000000908000000000207f7fa40000a04000001592a000a08000000000e32bfa9b0000d0400000003e8000e04000000c36f00150400000000000015080000000002160cf0180016040000006c3d00160800000000041f0b0c98001704000000029d001708000000000014d34c08001804000000000000180800000000019b47ccb8001d040000000000001d08000000000260c687b8001e040000006c45001e0800000000047bb9d668001f040000002c79002004000003b8be00210400000003e80029040000000000002908000000000097f628d0002a0400000077ea002a0800000000053ba6b680002b040000000312002b08000000000044f85090002c040000000000002c080000000000b3e6b70800310400000000000031080000000001087d1bb800320400000077f400320800000000054b48eab8003304000000313f003404000003b7e100350400000003e8003d040000000000003d080000000000be7e2408003e0400000074fd003e08000000000543edc2e8003f040000000000003f08000000000009a76148004004000000018800400800000000016fa7fa38004504000000000000450800000000016556b1a000460400000074ff004608000000000559c793a0004704000000306a004804000003b10900490400000003e890000000020e0d5200000000';
//        $hraw = '534d4100 0004 02a0 0000 0001 0042 0010 6073 3f88e87ff9d58ec4cd90d199e6246b36982ed0708fc4a4b695d81bd867d51822845594b74123621ae28c2d7e5ea4a05d750c256c948b7371c48b77df0256814200000000';
//        $hraw = '534d4100000402a0000000010042001060733f88e87ff9d58ec4cd90d199e6246b36982ed0708fc4a4b695d81bd867d51822845594b74123621ae28c2d7e5ea4a05d750c256c948b7371c48b77df0256814200000000';
        $this->SendDebug(sprintf('%s (%s)', __FUNCTION__, 'hraw'), $hraw, 0);

        //Erkennungsstring
        $offset = 0;
        $len    = 4;
        if ($this->ReadPropertyBoolean(self::PROP_ENTENDED_DEBUG_INFORMATION)) {
            $this->SendDebug(sprintf('%s (%s:%s)', __FUNCTION__, $offset, 'Erkennungsstring')
                , sprintf('%s (HEX: %s)', hex2bin(substr($hraw, $offset * 2, $len * 2)), substr($hraw, $offset * 2, $len * 2)), 0);
        }

        //Datenlänge/Tag
        $offset += $len;
        $len = 2;
        $dataLen = hexdec(substr($hraw, $offset * 2, $len * 2));
        if ($this->ReadPropertyBoolean(self::PROP_ENTENDED_DEBUG_INFORMATION)) {
            $this->SendDebug(
                sprintf('%s (%s:%s)', __FUNCTION__, $offset, 'Datenlänge'),
                (string)$dataLen,
                0
            );
        }

        //Tag
        $offset += $len;
        $len = $dataLen;
        if ($this->ReadPropertyBoolean(self::PROP_ENTENDED_DEBUG_INFORMATION)) {
            $this->SendDebug(sprintf('%s (%s)', __FUNCTION__, 'Tag')
                , substr($hraw, ($offset) * 2, $len * 2) , 0);
        }

        //gruppe
        $offset += $len;
        $len    = 2;
        if ($this->ReadPropertyBoolean(self::PROP_ENTENDED_DEBUG_INFORMATION)) {
            $this->SendDebug(sprintf('%s (%s:%s)', __FUNCTION__, $offset, 'Gruppe')
                , sprintf('%s',substr($hraw, $offset * 2, $len * 2)), 0);
        }

        //Datenlänge
        $offset += $len;
        $len = 2;
        $dataLen = hexdec(substr($hraw, $offset * 2, $len * 2));
        if ($this->ReadPropertyBoolean(self::PROP_ENTENDED_DEBUG_INFORMATION)) {
            $this->SendDebug(
                sprintf('%s (%s:%s)', __FUNCTION__, $offset, 'Datenlänge'),
                (string)$dataLen,
                0
            );
        }

        //Tag
        $offset += $len;
        $len = 2;
        if ($this->ReadPropertyBoolean(self::PROP_ENTENDED_DEBUG_INFORMATION)) {
            $this->SendDebug(
                sprintf('%s (%s:%s)', __FUNCTION__, $offset, 'Tag'),
                sprintf('%s', substr($hraw, $offset * 2, $len * 2)),
                0
            );
        }

        //ProtokollID
        $offset      += $len;
        $len         = 2;
        $protokollID = substr($hraw, $offset * 2, $len * 2);
        if ($this->ReadPropertyBoolean(self::PROP_ENTENDED_DEBUG_INFORMATION)) {
            $this->SendDebug(sprintf('%s (%s:%s)', __FUNCTION__, $offset, 'ProtokollID'), sprintf('%s', $protokollID), 0);
        }
        if ($protokollID !== '6069') {
            $this->SendDebug(sprintf('%s (%s:%s)', __FUNCTION__, $offset, 'ProtokollID')
                , sprintf('%s - ignored - ',substr($hraw, $offset * 2, $len * 2)), 0);
            return;
        }

        //zaehlerkennung
        $offset += $len;
        $len    = 6;
        if ($this->ReadPropertyBoolean(self::PROP_ENTENDED_DEBUG_INFORMATION)) {
            $this->SendDebug(sprintf('%s (%s:%s)', __FUNCTION__, $offset, 'Zählerkennung'), substr($hraw, $offset * 2, $len * 2), 0);
        }

        //Messzeitpunkt
        $offset += $len;
        $len    = 4;
        if ($this->ReadPropertyBoolean(self::PROP_ENTENDED_DEBUG_INFORMATION)) {
            $this->SendDebug(sprintf('%s (%s:%s)', __FUNCTION__, $offset, 'Messzeitpunkt'), base_convert(substr($hraw, $offset * 2, $len * 2), 16, 10), 0);
        }

        $offset   += $len;
        $finished = false;

        while (!$finished) {
            //obis Id
            $len = 4;
            $id  = strtolower(substr($hraw, $offset * 2, $len * 2));
            if ($this->ReadPropertyBoolean(self::PROP_ENTENDED_DEBUG_INFORMATION)) {
                $this->SendDebug(sprintf('%s (%s:%s)', __FUNCTION__, $offset, 'id'), $id, 0);
            }

            //echo $id . PHP_EOL;
            if (in_array($id, ['00000000', ''])) {
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
                $swVersion = sprintf(
                    '%s.%s.%s.%s',
                    hexdec(substr($swVersion, 0, 2)),
                    hexdec(substr($swVersion, 2, 2)),
                    hexdec(substr($swVersion, 4, 2)),
                    chr(hexdec(substr($swVersion, 6, 2)))
                );

                $this->SendDebug(
                    sprintf('%s (%s)', __FUNCTION__, 'SW-Version'),
                    $swVersion,
                    0
                );
                $this->setValue('SW_VERSION', $swVersion);
            } else {
                trigger_error(sprintf('id \'%s\' (Len=%s) unbekannt, hraw: %s', $id, strlen($id), $hraw));
                $finished = true;
            }
            $offset += $len;
        }
    }

    private function getSubstringFromHex(string $hraw, int $offset, int $len): string
    {
        return substr($hraw, $offset * 2, $len * 2);
    }

    private function setValueFromHexAndList(string $obisID, string $hraw, int $offset, int $len, string $prefix, array $list): void
    {
        $ident = $this->getIdent($prefix, $list[$obisID]['name']);
        if ($this->ReadPropertyBoolean(self::PROP_ENTENDED_DEBUG_INFORMATION)) {
            $this->SendDebug(
                sprintf('%s (%s)', __FUNCTION__, $obisID),
                sprintf(
                    '%s: %s, %s, %s',
                    $prefix,
                    (int)$this->ReadPropertyBoolean(self::PROP_SHOW_SINGLE_PHASES),
                    (int)$this->ReadPropertyBoolean(self::PROP_SHOW_DETAILED_CHANNELS),
                    (int)$list[$obisID]['detail']
                ),
                0
            );
        }

        if (($prefix === 'SUM' || $this->ReadPropertyBoolean(self::PROP_SHOW_SINGLE_PHASES))
            && ($this->ReadPropertyBoolean(self::PROP_SHOW_DETAILED_CHANNELS)
                || !$list[$obisID]['detail'])) {
            $reducedUpdateFrequency = $this->ReadPropertyInteger(self::PROP_EXTENDED_UPDATE_INTERVAL);
            if ($reducedUpdateFrequency > 0
                && IPS_GetVariable($this->GetIDForIdent($ident))['VariableUpdated'] > (time() - $reducedUpdateFrequency)) {
                return;
            }
            $this->SetValue(
                $ident,
                base_convert($this->getSubstringFromHex($hraw, $offset, $len), 16, 10) / $list[$obisID]['divisor']
            );
        }
    }
}