@once
    <style>
        .pms-ai-assistant-mark {
            display: grid;
            place-items: center;
            box-sizing: border-box;
            aspect-ratio: 1;
            border: 1px solid var(--ai-mark-border, #7189b4) !important;
            border-radius: 50% !important;
            overflow: hidden;
            background: #141a2a !important;
        }

        .pms-ai-assistant-mark > img {
            display: block;
            width: 100% !important;
            height: 100% !important;
            max-width: none !important;
            object-fit: cover;
            /* Center the round emblem in the existing Bitroxia artwork. */
            transform: translateY(11%) scale(1.84);
        }
    </style>
@endonce
