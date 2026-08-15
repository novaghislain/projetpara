<?php

namespace App\Http\Controllers\Gel\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Calendar\Event;
use App\Models\Calendar\EventAttendee;

class CalendarController extends Controller
{
    public function createEvent(Request $request)
    {
        $request->validate([
            'client_id' => 'required|uuid',
            'title' => 'required|string',
            'start_time' => 'required|date',
            'end_time' => 'required|date|after:start_time',
            'attendees' => 'nullable|array' // liste d'emails
        ]);

        $event = Event::create($request->all());

        if ($request->has('attendees')) {
            foreach ($request->attendees as $email) {
                EventAttendee::create([
                    'event_id' => $event->id,
                    'email' => $email
                ]);
            }
        }

        return response()->json([
            'status' => 'success',
            'data' => $event->load('attendees'),
            'message' => 'Événement créé dans l\'agenda.'
        ]);
    }

    public function updateAttendeeStatus(Request $request, $eventId)
    {
        $request->validate([
            'email' => 'required|email',
            'status' => 'required|in:accepted,declined'
        ]);

        $attendee = EventAttendee::where('event_id', $eventId)
                                 ->where('email', $request->email)
                                 ->firstOrFail();

        $attendee->update(['status' => $request->status]);

        return response()->json([
            'status' => 'success',
            'data' => $attendee,
            'message' => 'Statut de participation mis à jour.'
        ]);
    }
}
