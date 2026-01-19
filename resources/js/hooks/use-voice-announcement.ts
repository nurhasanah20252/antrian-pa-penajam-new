import { useCallback, useEffect, useRef, useState } from 'react';

import type { RecentlyCalledQueue } from '@/types';

interface UseVoiceAnnouncementOptions {
    enabled?: boolean;
    lang?: string;
    rate?: number;
    pitch?: number;
    volume?: number;
    audioOnly?: boolean;
}

interface UseVoiceAnnouncementReturn {
    isSupported: boolean;
    isEnabled: boolean;
    isSpeaking: boolean;
    toggleEnabled: () => void;
    announce: (text: string) => void;
}

/**
 * Hook for managing voice announcements using Web Speech API.
 * Automatically announces newly called queues.
 */
export function useVoiceAnnouncement(
    recentlyCalled: RecentlyCalledQueue[],
    options: UseVoiceAnnouncementOptions = {},
): UseVoiceAnnouncementReturn {
    const {
        enabled: initialEnabled = true,
        lang = 'id-ID',
        rate = 0.9,
        pitch = 1,
        volume = 1,
        audioOnly = false,
    } = options;

    const [isEnabled, setIsEnabled] = useState(initialEnabled);
    const [isSpeaking, setIsSpeaking] = useState(false);
    const announcedRef = useRef<Set<string>>(new Set());
    const queueRef = useRef<string[]>([]);
    const audioQueueRef = useRef<string[]>([]);
    const audioRef = useRef<HTMLAudioElement | null>(null);
    const isSpeakingRef = useRef(false);

    const isSupported = audioOnly
        ? typeof window !== 'undefined'
        : typeof window !== 'undefined' && 'speechSynthesis' in window;

    const playAudio = useCallback(
        (url: string) => {
            if (!isSupported || !isEnabled) return;

            if (!audioRef.current) {
                audioRef.current = new Audio();
            }

            const audio = audioRef.current;
            audio.src = url;

            audio.onplay = () => {
                setIsSpeaking(true);
                isSpeakingRef.current = true;
            };

            const handleFinish = () => {
                setIsSpeaking(false);
                isSpeakingRef.current = false;
                processQueue();
            };

            audio.onended = handleFinish;
            audio.onerror = handleFinish;

            audio.play().catch(handleFinish);
        },
        [isSupported, isEnabled],
    );

    const speak = useCallback(
        (text: string) => {
            if (!isSupported || !isEnabled) return;

            const utterance = new SpeechSynthesisUtterance(text);
            utterance.lang = lang;
            utterance.rate = rate;
            utterance.pitch = pitch;
            utterance.volume = volume;

            utterance.onstart = () => {
                setIsSpeaking(true);
                isSpeakingRef.current = true;
            };

            utterance.onend = () => {
                setIsSpeaking(false);
                isSpeakingRef.current = false;
                processQueue();
            };

            utterance.onerror = () => {
                setIsSpeaking(false);
                isSpeakingRef.current = false;
                processQueue();
            };

            window.speechSynthesis.speak(utterance);
        },
        [isSupported, isEnabled, lang, rate, pitch, volume],
    );

    const processQueue = useCallback(() => {
        if (isSpeakingRef.current) return;

        if (audioQueueRef.current.length > 0) {
            const nextAudio = audioQueueRef.current.shift();
            if (nextAudio) {
                playAudio(nextAudio);
            }
            return;
        }

        if (queueRef.current.length === 0) return;

        const nextText = queueRef.current.shift();
        if (nextText) {
            speak(nextText);
        }
    }, [playAudio, speak]);

    const announce = useCallback(
        (text: string) => {
            queueRef.current.push(text);
            if (!isSpeakingRef.current) {
                processQueue();
            }
        },
        [processQueue],
    );

    const announceAudio = useCallback(
        (url: string) => {
            audioQueueRef.current.push(url);
            if (!isSpeakingRef.current) {
                processQueue();
            }
        },
        [processQueue],
    );

    const formatAnnouncement = useCallback(
        (queue: RecentlyCalledQueue): string => {
            // Speech format requires spaces between letters for clearer pronunciation
            const number = queue.number
                .split('')
                .map((char) => (/[A-Z]/.test(char) ? char + ' ' : char))
                .join('');

            return `Nomor antrian ${number.trim()}, silakan menuju loket ${queue.counter}`;
        },
        [],
    );

    useEffect(() => {
        if (!isEnabled || !isSupported || recentlyCalled.length === 0) return;

        for (const queue of recentlyCalled) {
            const key = `${queue.number}-${queue.called_at}`;

            if (!announcedRef.current.has(key)) {
                announcedRef.current.add(key);

                if (queue.voice_url && audioOnly) {
                    announceAudio(queue.voice_url);
                } else {
                    const text = formatAnnouncement(queue);
                    announce(text);
                }
            }
        }

        if (announcedRef.current.size > 50) {
            const entries = Array.from(announcedRef.current);
            announcedRef.current = new Set(entries.slice(-30));
        }
    }, [
        recentlyCalled,
        isEnabled,
        isSupported,
        formatAnnouncement,
        announce,
        announceAudio,
        audioOnly,
    ]);

    const toggleEnabled = useCallback(() => {
        setIsEnabled((prev) => {
            if (prev && isSupported) {
                if (!audioOnly) {
                    window.speechSynthesis.cancel();
                }
                queueRef.current = [];
                audioQueueRef.current = [];
                if (audioRef.current) {
                    audioRef.current.pause();
                    audioRef.current.currentTime = 0;
                }
            }
            return !prev;
        });
    }, [isSupported, audioOnly]);

    return {
        isSupported,
        isEnabled,
        isSpeaking,
        toggleEnabled,
        announce,
    };
}
