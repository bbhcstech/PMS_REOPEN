<?php

return [
    'api_key' => env('GROQ_API_KEY'),
    'model' => env('GROQ_MODEL', 'openai/gpt-oss-120b'),
    // Tried in order when the configured model cannot answer.
    'fallback_models' => array_values(array_filter(array_map('trim', explode(',', (string) env('GROQ_FALLBACK_MODELS', 'openai/gpt-oss-120b,openai/gpt-oss-20b'))))),
    'timeout' => 30,
];
