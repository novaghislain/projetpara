<?php

namespace App\Http\Controllers\Gel\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Crm\Testimonial;

class TestimonialController extends Controller
{
    public function submitTestimonial(Request $request)
    {
        $request->validate([
            'client_id' => 'required|uuid',
            'author_name' => 'required|string',
            'author_company' => 'nullable|string',
            'author_position' => 'nullable|string',
            'rating' => 'required|integer|min:1|max:5',
            'content' => 'required|string'
        ]);

        $testimonial = Testimonial::create(array_merge($request->all(), ['status' => 'pending']));

        return response()->json([
            'status' => 'success',
            'data' => $testimonial,
            'message' => 'Merci ! Votre témoignage est en attente de modération.'
        ]);
    }

    public function getPublicTestimonials($clientId)
    {
        $testimonials = Testimonial::where('client_id', $clientId)
            ->where('status', 'approved')
            ->where('is_public', true)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $testimonials
        ]);
    }
}
