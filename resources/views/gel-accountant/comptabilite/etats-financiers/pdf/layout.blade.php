<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>@yield('title', 'État Financier')</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 11px;
            color: #000;
            margin: 0;
            padding: 0;
            line-height: 1.3;
        }
        @page {
            margin: 1cm;
        }
        .header {
            width: 100%;
            border-bottom: 2px solid #000;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        .header table {
            width: 100%;
            border-collapse: collapse;
            border: none;
        }
        .header td {
            vertical-align: top;
            border: none;
            padding: 0;
        }
        .cabinet-info {
            font-size: 10px;
            color: #333;
        }
        .cabinet-name {
            font-size: 14px;
            font-weight: bold;
            color: #000;
            margin-bottom: 4px;
        }
        .document-title {
            text-align: right;
        }
        .document-title h1 {
            font-size: 18px;
            margin: 0 0 5px 0;
            text-transform: uppercase;
        }
        .document-subtitle {
            font-size: 12px;
            font-weight: bold;
        }
        
        .grid-container {
            width: 100%;
        }
        
        .syscohada-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        .syscohada-table th, .syscohada-table td {
            border: 1px solid #000;
            padding: 6px 8px;
            text-align: right;
        }
        .syscohada-table th {
            background-color: #f0f0f0;
            font-weight: bold;
            text-align: center;
            text-transform: uppercase;
        }
        .syscohada-table td.text-left {
            text-align: left;
        }
        .syscohada-table td.text-center {
            text-align: center;
        }
        .syscohada-table tr.total-row td {
            font-weight: bold;
            background-color: #f8f8f8;
        }
        .syscohada-table tr.section-title td {
            font-weight: bold;
            text-align: left;
            background-color: #f0f0f0;
            text-transform: uppercase;
        }
        
        /* Layout colonnes */
        .col-half {
            width: 48%;
            display: inline-block;
            vertical-align: top;
        }
        .col-spacer {
            width: 3%;
            display: inline-block;
        }
        
        .footer {
            position: fixed;
            bottom: -10px;
            left: 0;
            right: 0;
            font-size: 9px;
            text-align: center;
            color: #666;
            border-top: 1px solid #ccc;
            padding-top: 5px;
        }
    </style>
</head>
<body>
    <div class="header">
        <table>
            <tr>
                <td width="33%" style="text-align: center;">
                    <div style="font-weight: bold; font-size: 11px;">RÉPUBLIQUE DU BÉNIN</div>
                    <div style="font-size: 10px; margin-top: 2px;">MINISTÈRE DE L'ÉCONOMIE ET DES FINANCES</div>
                    <div style="font-size: 10px; margin-top: 2px;">DIRECTION GÉNÉRALE DES IMPÔTS</div>
                    <div style="border-top: 1px solid #000; width: 60%; margin: 5px auto;"></div>
                </td>
                <td width="34%" style="text-align: center;">
                    <!-- Placeholder pour les armoiries ou logo de l'État -->
                    <div style="font-size: 24px;">🇧🇯</div>
                </td>
                <td width="33%" style="text-align: center;">
                    <div class="cabinet-name" style="font-size: 12px; margin-bottom: 2px;">
                        {{ auth()->user()->cabinet->nom ?? 'Cabinet Comptable' }}
                    </div>
                    <div class="cabinet-info" style="font-size: 10px;">
                        IFU : {{ auth()->user()->cabinet->ifu ?? 'N/A' }}<br>
                        Système : SYSCOHADA Révisé
                    </div>
                </td>
            </tr>
        </table>
        <div style="text-align: center; margin-top: 20px;">
            <h1 style="font-size: 18px; margin: 0; text-transform: uppercase; border: 1px solid #000; display: inline-block; padding: 5px 20px;">
                @yield('document_title')
            </h1>
            <div style="font-size: 12px; font-weight: bold; margin-top: 5px;">
                @yield('document_subtitle', 'Période : Exercice en cours')
            </div>
            <div style="font-size: 10px; margin-top: 5px;">
                Document généré le {{ date('d/m/Y à H:i') }}
            </div>
        </div>
    </div>

    <div class="content">
        @yield('content')
    </div>

    <div class="footer">
        Document certifié exact et conforme par le cabinet {{ auth()->user()->cabinet->nom ?? 'Cabinet Comptable' }}. 
        Généré par le compte de : <strong>{{ auth()->user()->name ?? 'Utilisateur' }}</strong> le {{ date('d/m/Y à H:i') }}.<br>
        <em>Para - Logiciel de gestion de cabinet</em>
    </div>
</body>
</html>
