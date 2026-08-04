@component('mail::message')
# 📅 Invitation : {{ $event->title }}

Bonjour **{{ $event->guest_name ?: 'Invité(e)' }}**,

Vous êtes invité(e) à participer à l'événement suivant organisé par **{{ $cabinetName }}** :

---

@component('mail::panel')
**📌 {{ $event->title }}**

🗓 **Date :** {{ \Carbon\Carbon::parse($event->start_at)->translatedFormat('l d F Y') }}
⏰ **Heure :** {{ \Carbon\Carbon::parse($event->start_at)->format('H:i') }}{{ $event->end_at ? ' – ' . \Carbon\Carbon::parse($event->end_at)->format('H:i') : '' }}
@if($event->location)
📍 **Lieu :** {{ $event->location }}
@endif
@if($event->visio_link)
🎥 **Lien visio :** [{{ $event->visio_type ? ucfirst($event->visio_type) : 'Rejoindre' }}]({{ $event->visio_link }})
@endif
@if($event->description)
📝 **Description :** {{ $event->description }}
@endif
@endcomponent

@if($event->visio_link)
@component('mail::button', ['url' => $event->visio_link, 'color' => 'primary'])
🎥 Rejoindre la visioconférence
@endcomponent
@endif

Merci de confirmer votre présence en répondant à cet email.

Cordialement,
**{{ $cabinetName }}**

@endcomponent
