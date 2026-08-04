<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Prendre Rendez-vous</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Outfit:wght@500;700;800&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary: #4F46E5; /* Indigo 600 */
            --primary-light: #818CF8; /* Indigo 400 */
            --primary-dark: #3730A3; /* Indigo 800 */
            --secondary: #EC4899; /* Pink 500 */
            --bg-dark: #0F172A; /* Slate 900 */
            --text-main: #1E293B; /* Slate 800 */
            --text-muted: #64748B; /* Slate 500 */
            --border: rgba(226, 232, 240, 0.6);
            --glass-bg: rgba(255, 255, 255, 0.85);
            --glass-border: rgba(255, 255, 255, 0.4);
        }

        body {
            font-family: 'Inter', sans-serif;
            margin: 0; padding: 0;
            min-height: 100vh;
            display: flex; align-items: center; justify-content: center;
            background: var(--bg-dark);
            /* Premium abstract gradient background */
            background-image: 
                radial-gradient(at 0% 0%, rgba(79, 70, 229, 0.4) 0px, transparent 50%),
                radial-gradient(at 100% 0%, rgba(236, 72, 153, 0.3) 0px, transparent 50%),
                radial-gradient(at 100% 100%, rgba(56, 189, 248, 0.3) 0px, transparent 50%),
                radial-gradient(at 0% 100%, rgba(139, 92, 246, 0.3) 0px, transparent 50%);
            background-size: cover;
            background-attachment: fixed;
            color: var(--text-main);
        }

        /* Animations */
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        @keyframes float {
            0% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
            100% { transform: translateY(0px); }
        }

        .container {
            width: 100%; max-width: 650px; padding: 40px 20px;
            animation: fadeInUp 0.8s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }

        .card {
            background: var(--glass-bg);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-radius: 24px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25), inset 0 1px 0 0 var(--glass-border);
            padding: 48px;
            border: 1px solid var(--glass-border);
            position: relative;
            overflow: hidden;
        }

        /* Decorative blobs inside card */
        .card::before {
            content: ''; position: absolute; top: -50px; right: -50px; width: 150px; height: 150px;
            background: linear-gradient(135deg, var(--secondary), var(--primary));
            border-radius: 50%; opacity: 0.1; filter: blur(30px); pointer-events: none;
        }

        .header { text-align: center; margin-bottom: 40px; position: relative; z-index: 1; }
        
        .icon-header {
            width: 80px; height: 80px; margin: 0 auto 24px;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white; border-radius: 24px;
            display: flex; align-items: center; justify-content: center;
            font-size: 32px; box-shadow: 0 10px 25px -5px rgba(79, 70, 229, 0.5);
            animation: float 6s ease-in-out infinite;
            transform-style: preserve-3d;
        }

        h1 { 
            font-family: 'Outfit', sans-serif; margin: 0 0 12px; font-size: 32px; font-weight: 800; 
            background: linear-gradient(135deg, var(--bg-dark), var(--primary-dark));
            -webkit-background-clip: text; -webkit-text-fill-color: transparent;
            letter-spacing: -0.5px;
        }
        
        p { margin: 0; color: var(--text-muted); font-size: 16px; line-height: 1.5; font-weight: 500; }
        
        .form-section { 
            margin-bottom: 32px; background: rgba(255, 255, 255, 0.5); 
            padding: 24px; border-radius: 16px; border: 1px solid var(--border);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .form-section:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px -10px rgba(0,0,0,0.05);
        }

        .form-section-title { 
            font-family: 'Outfit', sans-serif; font-size: 18px; font-weight: 700; 
            margin-bottom: 20px; display: flex; align-items: center; gap: 10px; color: var(--primary-dark);
        }
        .form-section-title i { 
            display: flex; align-items: center; justify-content: center;
            width: 32px; height: 32px; background: rgba(79, 70, 229, 0.1); 
            color: var(--primary); border-radius: 8px; font-size: 14px;
        }
        
        .form-group { margin-bottom: 20px; }
        label { display: block; font-size: 14px; font-weight: 600; margin-bottom: 8px; color: var(--text-main); }
        
        input, select, textarea {
            width: 100%; padding: 14px 16px; 
            background: white; border: 2px solid var(--border); border-radius: 12px;
            font-family: inherit; font-size: 15px; color: var(--text-main);
            box-sizing: border-box; transition: all 0.3s ease;
            box-shadow: 0 2px 4px rgba(0,0,0,0.02) inset;
        }
        input:focus, select:focus, textarea:focus { 
            outline: none; border-color: var(--primary); 
            box-shadow: 0 0 0 4px rgba(79, 70, 229, 0.15); 
            background: #fff;
        }
        
        .grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        
        .btn {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white; border: none; width: 100%; padding: 18px;
            border-radius: 14px; font-size: 16px; font-weight: 700; cursor: pointer; 
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            margin-top: 10px; display: flex; justify-content: center; align-items: center; gap: 10px;
            box-shadow: 0 10px 20px -5px rgba(79, 70, 229, 0.4);
            font-family: 'Outfit', sans-serif; letter-spacing: 0.5px; text-transform: uppercase;
        }
        .btn:hover { 
            transform: translateY(-3px); 
            box-shadow: 0 15px 25px -5px rgba(79, 70, 229, 0.5); 
            filter: brightness(1.1);
        }
        .btn:active { transform: translateY(1px); }
        
        /* Time Slots */
        .time-slots { display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px; }
        .time-slot {
            padding: 14px 0; border: 2px solid var(--border); border-radius: 12px;
            font-size: 15px; font-weight: 600; cursor: pointer; transition: all 0.2s ease;
            background: white; text-align: center; color: var(--text-main);
            position: relative; overflow: hidden;
        }
        .time-slot:hover { 
            border-color: var(--primary-light); color: var(--primary); 
            transform: translateY(-2px); box-shadow: 0 4px 12px rgba(0,0,0,0.05);
        }
        .time-slot.selected { 
            background: var(--primary); color: white; border-color: var(--primary); 
            box-shadow: 0 4px 15px rgba(79, 70, 229, 0.3);
            transform: scale(1.02);
        }
        
        /* Custom scrollbar for textarea */
        textarea::-webkit-scrollbar { width: 8px; }
        textarea::-webkit-scrollbar-track { background: transparent; }
        textarea::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }

        @media (max-width: 600px) {
            .card { padding: 30px 20px; }
            .grid-2 { grid-template-columns: 1fr; gap: 16px; }
            h1 { font-size: 26px; }
        }
    </style>
</head>
<body>

<div class="container">
    <div class="card">
        <div class="header">
            <div class="icon-header">
                <i class="far fa-calendar-check"></i>
            </div>
            <h1>Prendre rendez-vous</h1>
            <p>Sélectionnez un créneau pour échanger avec notre équipe. Nous vous répondrons dans les plus brefs délais.</p>
        </div>

        @if($errors->any())
            <div style="background:rgba(254, 242, 242, 0.8); color:#DC2626; border:1px solid #FECACA; padding:16px; border-radius:12px; margin-bottom:24px; font-size:14px; font-weight:500;">
                <ul style="margin:0; padding-left:24px;">
                    @foreach($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('public.booking.book', $cabinetId) }}" method="POST">
            @csrf
            
            <div class="form-section">
                <div class="form-section-title"><i class="fas fa-list-ul"></i> Motif du rendez-vous</div>
                <div class="form-group">
                    <select name="motif" required>
                        <option value="" disabled selected>Sélectionnez un motif d'intervention...</option>
                        @foreach($motifs as $m)
                            <option value="{{ $m }}">{{ $m }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="form-section">
                <div class="form-section-title"><i class="far fa-clock"></i> Date et Créneau horaire</div>
                <div class="form-group" style="margin-bottom: 24px;">
                    <label>Date souhaitée</label>
                    <input type="date" name="date" required min="{{ date('Y-m-d') }}">
                </div>
                
                <label>Créneaux disponibles</label>
                <div class="time-slots" id="time-slots">
                    <!-- Heures mockées -->
                    <div class="time-slot" data-time="09:00">09:00</div>
                    <div class="time-slot" data-time="10:00">10:00</div>
                    <div class="time-slot" data-time="11:00">11:00</div>
                    <div class="time-slot" data-time="14:00">14:00</div>
                    <div class="time-slot" data-time="15:00">15:00</div>
                    <div class="time-slot" data-time="16:00">16:00</div>
                </div>
                <input type="hidden" name="time" id="time-input" required>
            </div>

            <div class="form-section">
                <div class="form-section-title"><i class="far fa-user"></i> Vos coordonnées</div>
                <div class="grid-2">
                    <div class="form-group">
                        <label>Nom complet</label>
                        <input type="text" name="nom" required placeholder="Ex: Jean Dupont">
                    </div>
                    <div class="form-group">
                        <label>Email professionnel</label>
                        <input type="email" name="email" required placeholder="jean@entreprise.com">
                    </div>
                </div>
                <div class="form-group" style="margin-bottom: 0;">
                    <label>Informations complémentaires (optionnel)</label>
                    <textarea name="message" rows="3" placeholder="Précisez le contexte de votre demande pour mieux préparer notre échange..."></textarea>
                </div>
            </div>

            <button type="submit" class="btn">
                <span>Confirmer le rendez-vous</span>
                <i class="fas fa-arrow-right" style="font-size: 14px;"></i>
            </button>
        </form>
    </div>
</div>

<script>
    const timeSlots = document.querySelectorAll('.time-slot');
    const timeInput = document.getElementById('time-input');

    timeSlots.forEach(slot => {
        slot.addEventListener('click', () => {
            timeSlots.forEach(s => s.classList.remove('selected'));
            slot.classList.add('selected');
            timeInput.value = slot.dataset.time;
        });
    });
</script>

</body>
</html>
