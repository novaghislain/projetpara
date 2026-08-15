<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $title }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
            color: #000;
        }
        h1 {
            text-align: center;
            font-size: 16px;
            margin-bottom: 20px;
            text-transform: uppercase;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            border: 1px solid #000;
            padding: 8px 4px;
            text-align: left;
            vertical-align: top;
        }
        th {
            background-color: #f2f2f2;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 10px;
        }
    </style>
</head>
<body>
    <h1>{{ $title }}</h1>

    <table>
        <thead>
            <tr>
                @if($tab === 'arrivee')
                    <th>DATE D'ARRIVÉE</th>
                    <th>DATE DE LA CORRESPONDANCE</th>
                    <th>EXPÉDITEUR</th>
                    <th>OBJET</th>
                    <th>DATE ET N° DE LA RÉPONSE</th>
                @elseif($tab === 'depart')
                    <th>N° D'ORDRE</th>
                    <th>Nbre de PIÈCES</th>
                    <th>DATE DU DÉPART</th>
                    <th>DESTINATAIRE</th>
                    <th>OBJET</th>
                    <th>N° ARCHIVES</th>
                    <th>OBSERVATIONS</th>
                @else
                    <th>DATE</th>
                    <th>NUMÉRO D'ORDRE</th>
                    <th>NOMS ET ADRESSES</th>
                    <th>OBJET</th>
                    <th>NOMBRE DE PIÈCES</th>
                    <th>SIGNATURE DU DESTINATAIRE</th>
                @endif
            </tr>
        </thead>
        <tbody>
            @foreach($courriers as $courrier)
                <tr>
                    @if($tab === 'arrivee')
                        <td>{{ $courrier->date_reception ? \Carbon\Carbon::parse($courrier->date_reception)->format('d/m/Y') : '-' }}</td>
                        <td>{{ $courrier->date_courrier ? \Carbon\Carbon::parse($courrier->date_courrier)->format('d/m/Y') : '-' }}</td>
                        <td>{{ $courrier->expediteur ?? '-' }}</td>
                        <td>{{ $courrier->objet ?? '-' }}</td>
                        <td>
                            @if($courrier->date_reponse)
                                Date : {{ \Carbon\Carbon::parse($courrier->date_reponse)->format('d/m/Y') }}
                            @endif
                            @if($courrier->numero_reponse)
                                <br>N° : {{ $courrier->numero_reponse }}
                            @endif
                            @if(!$courrier->date_reponse && !$courrier->numero_reponse)
                                -
                            @endif
                        </td>
                    @elseif($tab === 'depart')
                        <td>{{ $courrier->numero_ordre ?? '-' }}</td>
                        <td>{{ $courrier->nombre_pieces ?? '0' }}</td>
                        <td>{{ $courrier->date_envoi ? \Carbon\Carbon::parse($courrier->date_envoi)->format('d/m/Y') : '-' }}</td>
                        <td>{{ $courrier->destinataire ?? '-' }}</td>
                        <td>{{ $courrier->objet ?? '-' }}</td>
                        <td>{{ $courrier->numero_archives ?? '-' }}</td>
                        <td>{{ $courrier->observations ?? '-' }}</td>
                    @else
                        <td>{{ $courrier->created_at->format('d/m/Y') }}</td>
                        <td>{{ $courrier->numero_ordre ?? '-' }}</td>
                        <td>{{ $courrier->noms_adresses ?? '-' }}</td>
                        <td>{{ $courrier->objet ?? '-' }}</td>
                        <td>{{ $courrier->nombre_pieces ?? '0' }}</td>
                        <td>{{ $courrier->signature_destinataire ?? '' }}</td>
                    @endif
                </tr>
            @endforeach
            @if($courriers->isEmpty())
                <tr>
                    <td colspan="7" style="text-align: center; font-style: italic;">Aucun enregistrement dans ce registre.</td>
                </tr>
            @endif
        </tbody>
    </table>
</body>
</html>
