import { Head, usePoll } from '@inertiajs/react';
import {
    Clock,
    Monitor,
    Users,
    Volume2,
    VolumeX,
    Wifi,
    WifiOff,
} from 'lucide-react';
import { useCallback, useState } from 'react';

import { useDisplayBoardWebSocket } from '@/hooks/use-display-board-websocket';
import { useVoiceAnnouncement } from '@/hooks/use-voice-announcement';
import { cn } from '@/lib/utils';
import type {
    DisplayBoardData,
    QueueStatistics,
    RecentlyCalledQueue,
} from '@/types';

interface DisplayIndexProps {
    services: DisplayBoardData[];
    statistics: QueueStatistics;
    recently_called: RecentlyCalledQueue[];
    last_updated: string;
}

function QueueDisplay({ data }: { data: DisplayBoardData }) {
    const currentQueue = data.current_queues[0];

    return (
        <div className="flex flex-col overflow-hidden rounded-2xl border-4 border-white/20 bg-white/10 backdrop-blur-sm">
            <div className="bg-primary p-4 text-center text-white">
                <h2 className="text-xl font-bold tracking-wide uppercase">
                    {data.service.name}
                </h2>
            </div>

            <div className="flex flex-1 flex-col items-center justify-center p-6">
                {currentQueue ? (
                    <>
                        <p className="mb-2 text-sm tracking-wide text-muted-foreground uppercase">
                            Nomor Antrian
                        </p>
                        <p className="text-7xl font-black tracking-tight text-primary">
                            {currentQueue.number}
                        </p>
                        <div className="mt-4 flex items-center gap-2 rounded-full bg-white/20 px-4 py-2">
                            <Monitor className="size-5" />
                            <span className="text-lg font-semibold">
                                Loket {currentQueue.counter_number}
                            </span>
                        </div>
                    </>
                ) : (
                    <div className="text-center">
                        <Clock className="mx-auto mb-2 size-12 text-muted-foreground" />
                        <p className="text-lg text-muted-foreground">
                            Menunggu panggilan...
                        </p>
                    </div>
                )}
            </div>

            <div className="flex items-center justify-center gap-2 bg-muted/50 p-3">
                <Users className="size-4" />
                <span className="text-sm font-medium">
                    {data.waiting_count} antrian menunggu
                </span>
            </div>
        </div>
    );
}

function StatBox({
    label,
    value,
    className,
}: {
    label: string;
    value: number | string;
    className?: string;
}) {
    return (
        <div
            className={cn(
                'rounded-xl bg-white/10 px-6 py-4 text-center backdrop-blur-sm',
                className,
            )}
        >
            <p className="text-sm tracking-wide text-muted-foreground uppercase">
                {label}
            </p>
            <p className="text-3xl font-bold">{value}</p>
        </div>
    );
}

function VoiceToggle({
    isEnabled,
    isSpeaking,
    isSupported,
    onToggle,
}: {
    isEnabled: boolean;
    isSpeaking: boolean;
    isSupported: boolean;
    onToggle: () => void;
}) {
    if (!isSupported) return null;

    return (
        <button
            onClick={onToggle}
            className={cn(
                'flex items-center gap-2 rounded-full px-4 py-2 transition-colors',
                isEnabled
                    ? 'bg-green-500/20 text-green-400 hover:bg-green-500/30'
                    : 'bg-red-500/20 text-red-400 hover:bg-red-500/30',
            )}
            title={isEnabled ? 'Nonaktifkan suara' : 'Aktifkan suara'}
        >
            {isEnabled ? (
                <>
                    <Volume2
                        className={cn('size-5', isSpeaking && 'animate-pulse')}
                    />
                    <span className="text-sm font-medium">Suara Aktif</span>
                </>
            ) : (
                <>
                    <VolumeX className="size-5" />
                    <span className="text-sm font-medium">Suara Mati</span>
                </>
            )}
        </button>
    );
}

function ConnectionStatus({ isConnected }: { isConnected: boolean }) {
    return (
        <div
            className={cn(
                'flex items-center gap-2 rounded-full px-3 py-1.5 text-xs font-medium',
                isConnected
                    ? 'bg-green-500/20 text-green-400'
                    : 'bg-yellow-500/20 text-yellow-400',
            )}
            title={isConnected ? 'WebSocket Terhubung' : 'Mode Polling'}
        >
            {isConnected ? (
                <>
                    <Wifi className="size-4" />
                    <span>Real-time</span>
                </>
            ) : (
                <>
                    <WifiOff className="size-4" />
                    <span>Polling</span>
                </>
            )}
        </div>
    );
}

export default function DisplayIndex({
    services,
    statistics,
    recently_called,
    last_updated,
}: DisplayIndexProps) {
    const [wsConnected, setWsConnected] = useState(false);
    const [wsCalledQueues, setWsCalledQueues] = useState<RecentlyCalledQueue[]>(
        [],
    );

    const handleQueueCalled = useCallback((queue: RecentlyCalledQueue) => {
        setWsConnected(true);
        setWsCalledQueues((prev) => [queue, ...prev].slice(0, 10));
    }, []);

    useDisplayBoardWebSocket(handleQueueCalled);

    usePoll(30000);

    const allRecentlyCalled = wsConnected
        ? [...wsCalledQueues, ...recently_called].slice(0, 10)
        : recently_called;

    const { isSupported, isEnabled, isSpeaking, toggleEnabled } =
        useVoiceAnnouncement(allRecentlyCalled, { audioOnly: true });

    const formattedTime = new Date(last_updated).toLocaleTimeString('id-ID', {
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit',
    });

    return (
        <>
            <Head title="Display Antrian" />

            <div className="flex min-h-screen flex-col bg-gradient-to-br from-slate-900 via-slate-800 to-slate-900 text-white">
                <header className="border-b border-white/10 bg-primary p-6 shadow-lg">
                    <div className="mx-auto flex max-w-7xl items-center justify-between">
                        <div>
                            <h1 className="text-3xl font-bold">
                                Pengadilan Agama Penajam
                            </h1>
                            <p className="text-primary-foreground/80">
                                Sistem Antrian Layanan PTSP
                            </p>
                        </div>
                        <div className="flex items-center gap-6">
                            <ConnectionStatus isConnected={wsConnected} />
                            <VoiceToggle
                                isEnabled={isEnabled}
                                isSpeaking={isSpeaking}
                                isSupported={isSupported}
                                onToggle={toggleEnabled}
                            />
                            <div className="text-right">
                                <p className="text-sm text-primary-foreground/80">
                                    Terakhir diperbarui
                                </p>
                                <p className="text-2xl font-bold">
                                    {formattedTime}
                                </p>
                            </div>
                        </div>
                    </div>
                </header>

                <main className="mx-auto flex w-full max-w-7xl flex-1 flex-col gap-6 p-6">
                    <div
                        className={cn(
                            'grid flex-1 gap-6',
                            services.length === 1 && 'grid-cols-1',
                            services.length === 2 && 'grid-cols-2',
                            services.length >= 3 && 'grid-cols-3',
                        )}
                    >
                        {services.map((service) => (
                            <QueueDisplay
                                key={service.service.id}
                                data={service}
                            />
                        ))}
                    </div>

                    <div className="grid grid-cols-5 gap-4">
                        <StatBox label="Menunggu" value={statistics.waiting} />
                        <StatBox label="Dipanggil" value={statistics.called} />
                        <StatBox
                            label="Diproses"
                            value={statistics.processing}
                        />
                        <StatBox label="Selesai" value={statistics.completed} />
                        <StatBox
                            label="Total Hari Ini"
                            value={statistics.total}
                        />
                    </div>
                </main>

                <footer className="border-t border-white/10 p-4 text-center">
                    <p className="text-sm text-muted-foreground">
                        Silakan menunggu nomor antrian Anda dipanggil. Terima
                        kasih.
                    </p>
                </footer>
            </div>
        </>
    );
}
