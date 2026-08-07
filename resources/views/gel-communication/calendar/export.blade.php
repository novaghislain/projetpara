<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <style>
        table { border-collapse: collapse; font-family: 'Times New Roman', serif; }
        th, td { border: 1px solid black; padding: 5px; }
        .text-center { text-align: center; }
        .align-middle { vertical-align: middle; }
    </style>
</head>
<body>

    <!-- Agenda Tab -->
    <table>
        <thead>
            <tr>
                <th style="width: 150px; background-color: #f3f3f3; font-weight: bold; text-align: center;">THÈME DU MOIS</th>
                <th style="width: 200px; background-color: #f3f3f3; font-weight: bold; text-align: center;">SEMAINE 1</th>
                <th style="width: 200px; background-color: #f3f3f3; font-weight: bold; text-align: center;">SEMAINE 2</th>
                <th style="width: 200px; background-color: #f3f3f3; font-weight: bold; text-align: center;">SEMAINE 3</th>
                <th style="width: 200px; background-color: #f3f3f3; font-weight: bold; text-align: center;">SEMAINE 4 (optionnel)</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>THÈMES</td>
                <td colspan="4">Les thèmes sont définis dans les contenus détaillés de l'onglet CE correspondant.</td>
            </tr>
            <tr>
                <td>Nouveauté IA</td>
                <td colspan="4">
                    <b>Explication du calendrier éditorial :</b><br>
                    <b>Lundi :</b> Dédié à des thématiques stratégiques autour de RéFIA comme la présentation, la gouvernance, et les partenariats.<br>
                    <b>Mercredi :</b> Axé sur les nouveautés IA dans le monde, avec des focus sur les dernières avancées et leurs impacts dans divers secteurs (éducation, santé, environnement).<br>
                    <b>Vendredi :</b> Focalisé sur des contenus plus personnels et interactifs, comme des interviews d'experts, des témoignages, et des retours sur les événements.
                </td>
            </tr>
        </tbody>
    </table>
    
    @foreach($platforms as $platform)
        <br><br>
        <table>
            <thead>
                <tr>
                    <th colspan="10" style="background-color: #800000; color: white; font-weight: bold; text-align: center;">{{ strtoupper($platform) }}</th>
                </tr>
                <tr>
                    <th colspan="10" style="background-color: #f4cccc; color: #800000; font-weight: bold; text-align: center;">PLANNING DE PUBLICATION DU -- / -- / --</th>
                </tr>
                <tr>
                    <th style="background-color: #f3f3f3; font-weight: bold; text-align: center;">Jour</th>
                    <th style="background-color: #f3f3f3; font-weight: bold; text-align: center;">Heure</th>
                    <th style="background-color: #f3f3f3; font-weight: bold; text-align: center;">Canal</th>
                    <th style="background-color: #f3f3f3; font-weight: bold; text-align: center;">Pilier</th>
                    <th style="background-color: #f3f3f3; font-weight: bold; text-align: center;">Thème</th>
                    <th style="background-color: #f3f3f3; font-weight: bold; text-align: center;">Description</th>
                    <th style="background-color: #f3f3f3; font-weight: bold; text-align: center;">Format</th>
                    <th style="background-color: #f3f3f3; font-weight: bold; text-align: center;">Mot. clés</th>
                    <th style="background-color: #f3f3f3; font-weight: bold; text-align: center;">RSD</th>
                    <th style="background-color: #f3f3f3; font-weight: bold; text-align: center;">Lien de la publication</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $platContents = $platformContents->get(strtolower($platform), collect());
                    // Group by Day of week
                    $groupedByDay = $platContents->groupBy(function($item) {
                        return $item->publish_date->translatedFormat('l');
                    });
                    // Force order: Lundi, Mardi, Mercredi...
                    $daysOrder = ['lundi', 'mardi', 'mercredi', 'jeudi', 'vendredi', 'samedi', 'dimanche'];
                    $sortedDays = $groupedByDay->sortBy(function($value, $key) use ($daysOrder) {
                        $pos = array_search(strtolower($key), $daysOrder);
                        return $pos === false ? 99 : $pos;
                    });
                @endphp
                
                @forelse($sortedDays as $dayName => $dayContents)
                    @foreach($dayContents as $idx => $content)
                    <tr>
                        @if($idx === 0)
                        <td rowspan="{{ $dayContents->count() }}" class="text-center align-middle" style="border-right: 2px solid #800000; font-weight:bold;">
                            {{ ucfirst($dayName) }}
                        </td>
                        @endif
                        <td class="text-center">{{ $content->publish_date->format('H\hi') }}</td>
                        <td class="text-center">{{ ucfirst($content->platform) }}</td>
                        <td>{{ $content->pillar ?? '' }}</td>
                        <td>{{ $content->theme ?? '' }}</td>
                        <td>{{ $content->description ?? '' }}</td>
                        <td class="text-center">{{ $content->content_format ?? '' }}</td>
                        <td class="text-center">{{ $content->keywords ?? '' }}</td>
                        <td class="text-center">{{ $content->rsd ?? '' }}</td>
                        <td class="text-center">{{ $content->published_link ?? '' }}</td>
                    </tr>
                    @endforeach
                    <tr><td colspan="10" style="background-color: #800000; height: 10px;"></td></tr>
                @empty
                    <tr>
                        <td colspan="10" class="text-center">Aucune publication planifiée pour ce canal.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    @endforeach

</body>
</html>
