<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\Muhurta\TarabalMurtiService;

class TarabalController extends Controller
{
    // ── POST /astro/tarabal-murti ─────────────────────────────────
    public function tarabalMurti(Request $request): \Illuminate\Http\JsonResponse
    {
        $v = $request->validate([
            'yr'         => 'required|integer|min:1900|max:2100',
            'mo'         => 'required|integer|min:1|max:12',
            'dy'         => 'required|integer|min:1|max:31',
            'lat'        => 'required|numeric',
            'lon'        => 'required|numeric',
            'utcOff'     => 'required|numeric',
            'birthNak'   => 'nullable|integer|min:-1|max:26',
            'birthRashi' => 'nullable|integer|min:-1|max:11',
        ]);

        $taraData = TarabalMurtiService::computeTarabal(
            (int)$v['yr'],
            (int)$v['mo'],
            (int)$v['dy'],
            (float)$v['lat'],
            (float)$v['lon'],
            (float)$v['utcOff'],
            (int)($v['birthNak'] ?? -1)
        );

        $murtiData = TarabalMurtiService::computeMurtiNirnaya(
            (int)$v['yr'],
            (int)$v['mo'],
            (int)$v['dy'],
            (float)$v['lat'],
            (float)$v['lon'],
            (float)$v['utcOff'],
            (int)($v['birthNak']   ?? -1),
            (int)($v['birthRashi'] ?? -1)
        );

        return response()->json(array_merge($taraData, [
            'murtiFormula'    => $murtiData['murtiFormula'],
            'murtiForAllVara' => $murtiData['murtiForAllVara'],
            'nakMuhurtaType'  => $murtiData['nakMuhurtaIdx'],
            'nakMuhurtaInfo'  => $murtiData['nakMuhurtaType'],
            'chandrabala'     => $murtiData['chandrabala'],
        ]));
    }
}
