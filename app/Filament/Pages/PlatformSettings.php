<?php

namespace App\Filament\Pages;

use App\Models\Setting;
use Filament\Actions\Action;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class PlatformSettings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';
    protected static ?string $navigationGroup = 'System';
    protected static ?string $navigationLabel = 'Settings';
    protected static ?int $navigationSort = 10;
    protected static string $view = 'filament.pages.platform-settings';

    public ?array $data = [];

    public function mount(): void
    {
        $this->data = [
            'platform_name' => (string) Setting::get('platform.name', config('app.name')),
            'platform_tagline' => (string) Setting::get('platform.tagline', 'Many Voices. One Adventist Sound.'),
            'fee_song' => (int) Setting::get('submissions.fee_song', 5500),
            'fee_album' => (int) Setting::get('submissions.fee_album', 15000),
            'fee_poem' => (int) Setting::get('submissions.fee_poem', 0),
            'fee_currency' => (string) Setting::get('submissions.fee_currency', 'MWK'),
            'refund_on_reject' => (bool) Setting::get('submissions.allow_refund_on_reject', false),
            'max_audio_mb' => (int) Setting::get('uploads.max_audio_mb', 50),
            'max_image_mb' => (int) Setting::get('uploads.max_image_mb', 5),
            'max_zip_mb' => (int) Setting::get('uploads.max_zip_mb', 200),
            'stream_min_seconds' => (int) Setting::get('streams.min_seconds_to_count', 30),
            'stream_cooldown_seconds' => (int) Setting::get('streams.cooldown_seconds', 120),
        ];
        $this->form->fill($this->data);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Tabs::make('Settings')
                    ->tabs([
                        Forms\Components\Tabs\Tab::make('Platform')
                            ->icon('heroicon-o-globe-alt')
                            ->schema([
                                Forms\Components\TextInput::make('platform_name')->required(),
                                Forms\Components\TextInput::make('platform_tagline')->maxLength(255),
                            ]),
                        Forms\Components\Tabs\Tab::make('Submissions & fees')
                            ->icon('heroicon-o-banknotes')
                            ->schema([
                                Forms\Components\Grid::make(3)->schema([
                                    Forms\Components\TextInput::make('fee_song')->label('Song fee')->numeric()->prefix('MK'),
                                    Forms\Components\TextInput::make('fee_album')->label('Album fee')->numeric()->prefix('MK'),
                                    Forms\Components\TextInput::make('fee_poem')->label('Poem fee')->numeric()->prefix('MK'),
                                ]),
                                Forms\Components\Select::make('fee_currency')
                                    ->options(['MWK' => 'MWK — Malawi Kwacha', 'USD' => 'USD — US Dollar'])
                                    ->default('MWK'),
                                Forms\Components\Toggle::make('refund_on_reject')
                                    ->label('Refund fee when a submission is rejected'),
                            ]),
                        Forms\Components\Tabs\Tab::make('Uploads')
                            ->icon('heroicon-o-cloud-arrow-up')
                            ->schema([
                                Forms\Components\Grid::make(3)->schema([
                                    Forms\Components\TextInput::make('max_audio_mb')->label('Max audio (MB)')->numeric(),
                                    Forms\Components\TextInput::make('max_image_mb')->label('Max image (MB)')->numeric(),
                                    Forms\Components\TextInput::make('max_zip_mb')->label('Max ZIP (MB)')->numeric(),
                                ]),
                            ]),
                        Forms\Components\Tabs\Tab::make('Streaming')
                            ->icon('heroicon-o-play')
                            ->schema([
                                Forms\Components\Grid::make(2)->schema([
                                    Forms\Components\TextInput::make('stream_min_seconds')
                                        ->label('Min seconds to count a play')
                                        ->numeric()
                                        ->helperText('Prevents refresh-abuse; 30 seconds is a sensible default'),
                                    Forms\Components\TextInput::make('stream_cooldown_seconds')
                                        ->label('Cooldown per session per song (seconds)')
                                        ->numeric(),
                                ]),
                            ]),
                    ])
                    ->contained(false),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();
        Setting::set('platform.name', $data['platform_name'], 'string', 'platform');
        Setting::set('platform.tagline', $data['platform_tagline'], 'string', 'platform');
        Setting::set('submissions.fee_song', $data['fee_song'], 'integer', 'submissions');
        Setting::set('submissions.fee_album', $data['fee_album'], 'integer', 'submissions');
        Setting::set('submissions.fee_poem', $data['fee_poem'], 'integer', 'submissions');
        Setting::set('submissions.fee_currency', $data['fee_currency'], 'string', 'submissions');
        Setting::set('submissions.allow_refund_on_reject', $data['refund_on_reject'], 'boolean', 'submissions');
        Setting::set('uploads.max_audio_mb', $data['max_audio_mb'], 'integer', 'uploads');
        Setting::set('uploads.max_image_mb', $data['max_image_mb'], 'integer', 'uploads');
        Setting::set('uploads.max_zip_mb', $data['max_zip_mb'], 'integer', 'uploads');
        Setting::set('streams.min_seconds_to_count', $data['stream_min_seconds'], 'integer', 'streams');
        Setting::set('streams.cooldown_seconds', $data['stream_cooldown_seconds'], 'integer', 'streams');

        Notification::make()->title('Settings saved')->success()->send();
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('save')
                ->label('Save changes')
                ->icon('heroicon-o-check')
                ->action('save'),
        ];
    }
}
