#!/usr/bin/env node

const [, , text, outputPath, voice = 'id-ID-GadisNeural'] = process.argv;

if (!text || !outputPath) {
    console.error('Usage: tts-generate <text> <outputPath> [voice]');
    process.exit(1);
}

try {
    const tts = new EdgeTTS({
        voice,
        lang: 'id-ID',
        outputFormat: 'audio-24khz-48kbitrate-mono-mp3',
    });

    await tts.ttsPromise(text, outputPath);
} catch (error) {
    console.error(error?.message || 'Gagal membuat audio TTS');
    process.exit(1);
}
