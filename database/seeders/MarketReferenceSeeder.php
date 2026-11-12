<?php

namespace Database\Seeders;

use App\Models\Building;
use App\Models\Stall;
use Illuminate\Database\Seeder;

class MarketReferenceSeeder extends Seeder
{
    public function run(): void
    {
        $buildings = [
            'A' => ['Groceries & Vegetable Stall', 'Daily-cash ticket stalls and monthly stalls transcribed from the supplied layouts.'],
            'B' => ['Groceries', 'Groceries building layout transcribed from the supplied reference.'],
            'C' => ['Dry Goods', 'Dry goods building layout transcribed from the supplied reference.'],
            'D' => ['Dry Goods', 'Dry goods building layout transcribed from the supplied reference.'],
            'E' => ['Dry Goods Section', 'Dry goods stalls transcribed from the supplied Building E references.'],
        ];

        foreach ($buildings as $code => [$name, $description]) {
            Building::firstOrCreate(['code' => $code], [
                'name' => 'Building ' . $code . ' — ' . $name,
                'description' => $description,
                'is_active' => true,
            ]);
        }

        $sets = [
            'A' => [
                'range' => [1, 28],
                'rate' => 20,
                'frequency' => 'daily',
                'type' => 'Daily-Cash Ticket',
                'occupants' => [
                    1 => 'Mayflor Ramos', 2 => 'Myrna Urmatam',
                    4 => 'Jessa Mae Ugalde', 5 => 'Manuel Javier', 6 => 'Marilyn Casuay',
                    7 => 'Marilou Balisi', 8 => 'Nely Unday', 9 => 'Gladys Oarde',
                    10 => 'Bejean Mongcal', 11 => 'Criselda Juan', 12 => 'Criselda Juan',
                    13 => 'Concordia Urbis', 14 => 'Maylene Alejandro',
                    15 => 'Mildred Bermusio', 16 => 'Lailanie Antonio',
                    17 => 'Glory Pascua', 18 => 'Elsie Sacramento',
                    19 => 'Manuel Javier', 20 => 'Manuel Javier',
                    21 => 'Maricel Soliven', 22 => 'Elsie Sacramento',
                    23 => 'Adelina Mafet', 24 => 'Ariel Manimtim',
                    25 => 'Analyn Manimtim', 26 => 'Aileen Danao',
                    27 => 'Aileen Danao', 28 => 'Aileen Danao',
                ],
                'shared' => [
                    3 => 'A-03-04', 4 => 'A-03-04',
                    19 => 'A-19-20', 20 => 'A-19-20',
                    27 => 'A-27-28', 28 => 'A-27-28',
                ],
                'additional' => [
                    132 => ['Myrna Urmatam', 600], 133 => ['Lydia Galapia', 600],
                    134 => ['Ferdelina Tabao', 600], 135 => ['Evelyn Tabao', 600],
                    136 => ['Lolita Garcia', 600], 137 => ['Lolita Garcia', 600],
                    138 => ['Amy de los Santos', 600], 139 => ['Amy de los Santos', 600],
                ],
                'verify' => [3 => 'Name is printed across the combined 3–4 stall unit; confirm exact stall allocation.'],
            ],
            'B' => [
                'range' => [164, 195], 'rate' => 600, 'frequency' => 'monthly', 'type' => 'Groceries',
                'occupants' => [
                    164 => 'Miguel Roxas', 165 => 'Randy Porsenido', 166 => 'Randy Porsenido',
                    167 => 'Jessica Manansala', 168 => 'Rowena Manansala', 169 => 'Miriam Fabro',
                    170 => 'Palangcoy', 171 => 'Palangcoy', 172 => 'Rose Ann Manansala',
                    173 => 'Naiza Fabro', 174 => 'Gina Amit', 175 => 'Joe Palangcoy',
                    176 => 'Rowena Manansala', 177 => 'Cherry Miranda', 178 => 'Ian Fred Obani',
                    179 => 'Bunice Ulangco', 180 => 'Ederlina Uy', 181 => 'Ernalyn Viernes',
                    182 => 'Roda Cabal (Storage)', 183 => 'Susana Nolasco', 184 => 'Regina Cortez',
                    185 => 'Ofelia Martinez', 186 => 'Ofelia Martinez', 187 => 'Rose Ann Manansala',
                    188 => 'Abigail Casaway', 189 => 'Abigail Casaway', 190 => 'Genevieve Andasan',
                    191 => 'Regina Cortez', 194 => 'Ernalyn Viernes', 195 => 'Ederlina Uy',
                ],
                'verify' => [
                    170 => 'Handwritten annotation overlaps printed name; confirm occupant.',
                    171 => 'Handwritten annotation overlaps printed name; confirm occupant.',
                    174 => 'Handwritten correction is not fully legible; confirm occupant.',
                    192 => 'Handwritten occupant name is not reliably legible.',
                    193 => 'Handwritten occupant name is not reliably legible.',
                ],
                'shared' => [],
            ],
            'C' => [
                'range' => [69, 103], 'rate' => 600, 'frequency' => 'monthly', 'type' => 'Dry Goods',
                'occupants' => [
                    69 => 'Rico Cabote', 70 => 'Rico Cabote', 71 => 'Lectical Lebones',
                    72 => 'Lectical Lebones', 73 => 'Rhea Estal', 74 => 'Rhea Estal',
                    75 => 'Valen Urbis', 76 => 'Divina Cabote', 77 => 'Divina Cabote',
                    78 => 'Eormarie Adato', 79 => 'Renario Estal', 80 => 'Renario Estal',
                    81 => 'Warda Madale', 82 => 'Fahad Azis Anteng',
                    83 => 'Almira Ibrahim', 84 => 'Carmina Cocos', 85 => 'Analiza Oandasan',
                    86 => 'Ania Abolias', 88 => 'Nelda Luna', 89 => 'Nelda Luna',
                    90 => 'Felicidad Lomaat', 91 => 'Reynaldo Organya', 92 => 'Lila Conde',
                    93 => 'Lila Conde', 94 => 'Jovita Oandasan', 95 => 'Analiza Oandasan',
                    96 => 'Sandong Calit', 97 => 'Calli Sorpodra Paco',
                    98 => 'Peachy Oandasan', 99 => 'Peachy Oandasan',
                    101 => 'Leticia Muramita', 102 => 'Federico Urbemeta', 103 => 'Federico Urbemeta',
                ],
                'vacant' => [87 => 'Printed record is crossed out and handwritten replacement is unclear.'],
                'verify' => [
                    78 => 'Occupant spelling is unclear in the source image.',
                    81 => 'Occupant spelling is unclear in the source image.',
                    84 => 'Handwritten correction is not fully legible; confirm occupant.',
                    87 => 'Printed record is crossed out and handwritten replacement is unclear.',
                    90 => 'Occupant spelling is unclear in the source image.',
                    97 => 'Occupant spelling is unclear in the source image.',
                    100 => 'Handwritten occupant name is not reliably legible.',
                ],
                'shared' => [],
            ],
            'D' => [
                'range' => [104, 131], 'rate' => 600, 'frequency' => 'monthly', 'type' => 'Dry Goods',
                'occupants' => [
                    104 => 'Krissa Tagumasi', 105 => 'Sohair Adato', 106 => 'Sauda Adatu',
                    108 => 'Sainor Madale', 109 => 'Daudhadji Moti', 110 => 'Karen Ann Castillo',
                    111 => 'Karen Ann Castillo', 112 => 'Anamina Diamla', 113 => 'Macacuna Pacasum',
                    114 => 'Noron Ditual', 115 => 'Noron Ditual', 116 => 'Willy Doroca',
                    117 => 'Willy Doroca', 118 => 'Nora Enidal', 119 => 'Juanita Salabao',
                    120 => 'Jasmin Ameril', 121 => 'Mahdi Faisal', 122 => 'Sarah Abdulah',
                    123 => 'Sarah Abdulah', 124 => 'Allan Agni Guinal', 125 => 'Allan Agni Guinal',
                    126 => 'Myrah B. Guinal', 127 => 'Myrah B. Guinal', 128 => 'Nadjerah Cawinaga',
                    129 => 'Noron Salam Tandoc', 130 => 'Marissa Agluba', 131 => 'Assima Enidal',
                ],
                'vacant' => [107 => 'Printed VACANT label; handwritten replacement is not legible.'],
                'verify' => [107 => 'Printed VACANT label conflicts with handwritten name; verify before offering.'],
                'shared' => [],
                'additional' => [
                    '237' => ['Eduardo Frias', 400, 'Rice & Gilingan'],
                    '238' => ['Catherine Pablo', 400, 'Rice & Gilingan'],
                    '239' => ['Catherine Pablo', 400, 'Rice & Gilingan'],
                    '240' => ['Teresita Capulong', 400, 'Rice & Gilingan'],
                    '241' => ['Maribeth Mabbon', 400, 'Rice & Gilingan'],
                    '242' => ['Emelda Obani', 400, 'Rice & Gilingan'],
                    '243' => ['Cherry Miranda', 400, 'Rice & Gilingan'],
                    '244' => ['Aurora Velasco', 400, 'Rice & Gilingan'],
                    '245' => ['Shirley Tuliao', 400, 'Rice & Gilingan'],
                    '246' => ['Melanie Praderio', 600, 'Rice & Gilingan'],
                ],
            ],
            'E' => [
                'range' => [290, 318], 'rate' => 600, 'frequency' => 'monthly', 'type' => 'Dry Goods',
                'occupants' => [
                    290 => 'Felipa Michelle Ramos', 291 => 'Felipa Michelle Ramos',
                    292 => 'Hamdan Alikan', 293 => 'Jamail Moti',
                    294 => 'Asnawi Macacuna', 295 => 'Asnawi Macacuna',
                    296 => 'Lilibeth Castillo', 297 => 'Lilibeth Castillo',
                    298 => 'Jamail Moti', 299 => 'Ismael Ali', 300 => 'Ali, Isn(a)irah Polayagan',
                    301 => 'Rayan Galero', 302 => 'Farida Dimasangca', 303 => 'Farida Dimasangca',
                    304 => 'Noraima Enidal', 305 => 'Ongcad Enidal', 306 => 'Shelalaine Miguel',
                    307 => 'Shelalaine Miguel', 308 => 'Shelalaine Miguel', 309 => 'Mary Joy Cortez',
                    310 => 'Aurora Cabaddu', 311 => 'Miguel Roxas', 312 => 'Farhan Madale',
                    313 => 'Farhan Madale', 314 => 'Jahara Anteng', 315 => 'Sonia Zabala',
                    316 => 'Leona de Torres', 317 => 'Leona de Torres', 318 => 'Angelina Ramos',
                ],
                'verify' => [
                    300 => 'Given name spelling is unclear in the image.',
                    305 => 'Orange handwritten/highlighted annotation; occupant shown as printed, verify record.',
                ],
                'shared' => [],
                'additional' => [
                    '228' => ['Venice Clare Manalo', 600], '229' => ['Sandong Noth Calit', 600],
                    '230' => ['Almira Ibrahim', 1200], '231' => [null, 1200],
                    '232' => ['Maribel Orteza', 1200], '233-A' => ['Michael Noriega', 1200],
                    '233-B' => [null, 1200], '233-C' => [null, 1200], '233-D' => ['Leobme Omecsas', 1200],
                    '234' => ['Helen Noriega', 1200], '235' => ['Salani Sultan', 1000],
                    '236' => ['Macuy Pepito Abdun', 1000],
                    '247' => ['Marilou Lazaro', 450], '248' => ['Visitation Era', 450],
                    '249' => ['Aurora Alonzo', 450], '250' => ['Marites Jacob', 450],
                    '251' => [null, 450], '252' => ['James Martinez', 450],
                    '253' => ['Carolina Martinez', 450], '254' => ['Leonila Torda', 450],
                    '255' => ['Feliciana Santiago', 450], '256' => ['Maria Alcesto', 450],
                    '257' => ['Mario Luczon', 450], '258' => ['Mario Luczon', 450],
                    '259' => ['Feliciana Santiago', 450], '260' => ['Elenita Tumanquil (Storage)', 450],
                    '261' => ['Corazon Estela', 450], '262' => ['Corazon Estela', 450],
                    '263' => ['Michael Noriega (Storage)', 450], '264' => [null, 450],
                    '265' => [null, 450], '266' => ['Roda Cabal', 450],
                    '267' => ['Maribel Orteza', 1200], '268' => ['Maribel Orteza', 1200],
                    '269' => ['Elenita Tumanquil', 600], '270' => ['Luz Talosig', 600],
                    '271' => ['Amelia Luczon', 450], '272' => ['Domingo Gumiran', 450],
                ],
                'additional_verify' => [
                    '231' => 'Shared/overlapping source box; occupant assignment unclear.',
                    '233-B' => 'No legible occupant recorded in the source image.',
                    '233-C' => 'No legible occupant recorded in the source image.',
                    '233-D' => 'Occupant spelling is unclear in the source image.',
                ],
                'additional_vacant' => [
                    '231' => 'Shared/overlapping source box; verify whether this space is vacant.',
                    '233-B' => 'No legible occupant recorded in the source image.',
                    '233-C' => 'No legible occupant recorded in the source image.',
                ],
                'additional_explicit_vacant' => ['251', '264', '265'],
            ],
        ];

        foreach ($sets as $code => $set) {
            $building = Building::where('code', $code)->firstOrFail();
            [$from, $to] = $set['range'];
            for ($number = $from; $number <= $to; $number++) {
                $this->saveStall(
                    $building,
                    (string) $number,
                    $set['occupants'][$number] ?? null,
                    $set['rate'],
                    $set['frequency'],
                    $set['type'],
                    $set['verify'][$number] ?? null,
                    $set['shared'][$number] ?? null,
                    isset($set['vacant'][$number]),
                    $set['vacant'][$number] ?? null,
                    $number,
                );
            }

            foreach ($set['additional'] ?? [] as $number => $record) {
                $occupant = $record[0];
                $rate = $record[1];
                $type = $record[2] ?? $set['type'];
                $verify = $set['additional_verify'][$number] ?? null;
                $this->saveStall(
                    $building,
                    (string) $number,
                    $occupant,
                    $rate,
                    'monthly',
                    $type,
                    $verify,
                    null,
                    isset($set['additional_vacant'][$number]),
                    $set['additional_vacant'][$number] ?? null,
                    is_numeric($number) ? (int) $number : 233,
                    in_array((string) $number, $set['additional_explicit_vacant'] ?? [], true),
                );
            }
        }
    }

    private function saveStall(
        Building $building,
        string $number,
        ?string $occupant,
        int $rate,
        string $frequency,
        string $type,
        ?string $verificationNote,
        ?string $sharedGroup,
        bool $vacancyUnclear,
        ?string $vacancyNote,
        int $displayOrder,
        bool $explicitlyVacant = false,
    ): void {
        $needsVerification = $verificationNote !== null || $vacancyUnclear || $occupant === null && $vacancyNote === null && !$explicitlyVacant;
        $status = $needsVerification ? 'inactive' : ($occupant ? 'occupied' : ($explicitlyVacant ? 'available' : 'inactive'));
        $notes = $verificationNote ?? $vacancyNote;
        if ($explicitlyVacant && $notes === null) {
            $notes = 'Marked VACANT in the supplied reference layout.';
        }
        if ($occupant === null && $notes === null) {
            $notes = 'No occupant or explicit vacant label is legible in the supplied reference.';
        }

        Stall::firstOrCreate(['stall_number' => $number], [
            'building_id' => $building->id,
            'location' => 'Building ' . $building->code,
            'size_sqm' => 0,
            'monthly_rate' => $rate,
            'rental_rate' => $rate,
            'payment_frequency' => $frequency,
            'stall_type' => $type,
            'shared_group' => $sharedGroup,
            'shared_capacity' => 1,
            'source_occupant_name' => $occupant,
            'needs_verification' => $needsVerification,
            'source_notes' => $notes,
            'display_order' => $displayOrder,
            'description' => 'Transcribed from the supplied municipal market reference layout.',
            'status' => $status,
        ]);
    }
}
