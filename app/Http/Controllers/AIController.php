<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class AIController extends Controller
{
    public function ask(Request $request)
    {
        $message = $request->input('message');

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . env('GROQ_API_KEY'),
            'Content-Type' => 'application/json',
        ])->post('https://api.groq.com/openai/v1/chat/completions', [
            'model' => 'llama3-70b-8192',
            'messages' => [
                [
                    'role' => 'system',
                    'content' => 'You are a smart assistant. Please always reply in the same language the user uses. If the user types in Chinese, reply in Chinese. If the user types in English, reply in English.（你是一个智能助理。请始终用与用户相同的语言回答问题。如果用户使用中文，就用中文回答；如果使用英文，就用英文回答。）'
                ],
                [
                    'role' => 'user',
                    'content' => $message,
                ],
            ],
            'temperature' => 0.7,
        ]);

        return response()->json($response->json());
    }

    public function checkGrammar(Request $request)
    {
        $text = $request->input('text');

        $response = Http::get('https://api.textgears.com/grammar', [
            'text' => $text,
            'language' => 'en-US', 
            'key' => 'bhH9Np0rVrC0DBc3', 
        ]);

        return response()->json($response->json());
    }

    // public function checkGrammar(Request $request)
    // {
    //     $response = Http::asForm()->post('https://api.languagetool.org/v2/check', [
    //         'text' => $request->input('text'),
    //         'language' => 'en-US',
    //     ]);

    //     return response()->json($response->json());
    // }

}
