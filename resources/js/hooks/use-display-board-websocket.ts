import { router } from '@inertiajs/react';
import { useEchoPublic } from '@laravel/echo-react';
import { useCallback, useRef } from 'react';

import type { QueueStatistics, RecentlyCalledQueue } from '@/types';

interface QueueCalledPayload {
    number: string;
    counter: number;
    service_name: string;
    called_at: string;
    voice_url?: string | null;
}

interface DisplayUpdatedPayload {
    statistics: QueueStatistics;
    last_updated: string;
}

export function useDisplayBoardWebSocket(
    onQueueCalled?: (queue: RecentlyCalledQueue) => void,
): void {
    const recentlyCalledRef = useRef<RecentlyCalledQueue[]>([]);

    useEchoPublic<QueueCalledPayload>(
        'display',
        '.queue.called',
        useCallback(
            (event: QueueCalledPayload) => {
                const queue: RecentlyCalledQueue = {
                    number: event.number,
                    counter: event.counter,
                    service_name: event.service_name,
                    called_at: event.called_at,
                    voice_url: event.voice_url ?? null,
                };

                recentlyCalledRef.current = [
                    queue,
                    ...recentlyCalledRef.current,
                ].slice(0, 10);

                onQueueCalled?.(queue);

                router.reload({
                    only: ['services', 'statistics', 'recently_called'],
                });
            },
            [onQueueCalled],
        ),
    );

    useEchoPublic<DisplayUpdatedPayload>(
        'display',
        '.display.updated',
        useCallback(() => {
            router.reload({ only: ['services', 'statistics'] });
        }, []),
    );
}
