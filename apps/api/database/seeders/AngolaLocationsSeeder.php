<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AngolaProvince;
use App\Models\AngolaMunicipality;

class AngolaLocationsSeeder extends Seeder
{
    public function run(): void
    {
        if (AngolaProvince::count() > 0) {
            return;
        }

        // 18 Províncias de Angola
        $provinces = [
            ['name' => 'Bengo', 'code' => 'BGO', 'capital' => 'Caxito'],
            ['name' => 'Benguela', 'code' => 'BGU', 'capital' => 'Benguela'],
            ['name' => 'Bié', 'code' => 'BIE', 'capital' => 'Kuito'],
            ['name' => 'Cabinda', 'code' => 'CAB', 'capital' => 'Cabinda'],
            ['name' => 'Cuando Cubango', 'code' => 'CCU', 'capital' => 'Menongue'],
            ['name' => 'Cuanza Norte', 'code' => 'CNO', 'capital' => 'N\'dalatando'],
            ['name' => 'Cuanza Sul', 'code' => 'CUS', 'capital' => 'Sumbe'],
            ['name' => 'Cunene', 'code' => 'CNN', 'capital' => 'Ondjiva'],
            ['name' => 'Huambo', 'code' => 'HUA', 'capital' => 'Huambo'],
            ['name' => 'Huíla', 'code' => 'HUI', 'capital' => 'Lubango'],
            ['name' => 'Luanda', 'code' => 'LUA', 'capital' => 'Luanda'],
            ['name' => 'Lunda Norte', 'code' => 'LNO', 'capital' => 'Dundo'],
            ['name' => 'Lunda Sul', 'code' => 'LSU', 'capital' => 'Saurimo'],
            ['name' => 'Malanje', 'code' => 'MAL', 'capital' => 'Malanje'],
            ['name' => 'Moxico', 'code' => 'MOX', 'capital' => 'Luena'],
            ['name' => 'Namibe', 'code' => 'NAM', 'capital' => 'Moçâmedes'],
            ['name' => 'Uíge', 'code' => 'UIG', 'capital' => 'Uíge'],
            ['name' => 'Zaire', 'code' => 'ZAI', 'capital' => 'M\'banza-Kongo'],
        ];

        foreach ($provinces as $province) {
            $p = AngolaProvince::create($province);
            
            // Criar municípios principais para cada província
            $this->createMunicipalities($p);
        }
    }

    private function createMunicipalities($province): void
    {
        $municipalities = $this->getMunicipalitiesByProvince($province->code);
        
        foreach ($municipalities as $municipality) {
            AngolaMunicipality::create([
                'province_id' => $province->id,
                'name' => $municipality,
                'code' => strtoupper($province->code . '-' . substr(md5($municipality), 0, 5)),
            ]);
        }
    }

    private function getMunicipalitiesByProvince(string $provinceCode): array
    {
        $data = [
            'LUA' => ['Luanda', 'Cacuaco', 'Viana', 'Belas', 'Icolo e Bengo', 'Quiçama'],
            'BGU' => ['Benguela', 'Lobito', 'Catumbela', 'Ganda', 'Balombo'],
            'HUA' => ['Huambo', 'Caála', 'Londuimbali', 'Longonjo', 'Chinjenje'],
            'HUI' => ['Lubango', 'Bibala', 'Humpata', 'Quilengues', 'Matala'],
            'CAB' => ['Cabinda', 'Cacongo', 'Buco-Zau', 'Belize'],
            'BGO' => ['Caxito', 'Dande', 'Ambriz', 'Bula Atumba', 'Pango Aluquém'],
            'BIE' => ['Kuito', 'Andulo', 'Camacupa', 'Catabola', 'Nharea'],
            'CCU' => ['Menongue', 'Dirico', 'Mavinga', 'Nancova', 'Cuangar'],
            'CNO' => ['N\'dalatando', 'Ambaca', 'Banga', 'Bolungo', 'Cambambe'],
            'CUS' => ['Sumbe', 'Amboim', 'Seles', 'Ebo', 'Libolo'],
            'CNN' => ['Ondjiva', 'Ombadja', 'Cahama', 'Curoca', 'Cuvelai'],
            'LNO' => ['Dundo', 'Lucapa', 'Cambulo', 'Capenda-Camulemba', 'Caungula'],
            'LSU' => ['Saurimo', 'Cacolo', 'Dala', 'Muconda', 'Sombo'],
            'MAL' => ['Malanje', 'Cacuso', 'Calandula', 'Cangandala', 'Cacuso'],
            'MOX' => ['Luena', 'Alto Zambeze', 'Bundas', 'Camanongue', 'Léua'],
            'NAM' => ['Moçâmedes', 'Bibala', 'Camucuio', 'Virei', 'Tômbua'],
            'UIG' => ['Uíge', 'Ambuíla', 'Bembe', 'Damba', 'Maquela do Zombo'],
            'ZAI' => ['M\'banza-Kongo', 'Cuimba', 'Nóqui', 'Soyo', 'Tomboco'],
        ];

        return $data[$provinceCode] ?? [];
    }
}
