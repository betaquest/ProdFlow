<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProgramaResource\Pages;
use App\Models\Programa;
use App\Models\User;
use App\Models\Fase;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ProgramaResource extends Resource
{
    protected static ?string $model = Programa::class;

    // protected static ?string $navigationIcon = 'heroicon-o-rectangle-group';

    protected static ?string $navigationLabel = 'Programas';

    protected static ?string $navigationGroup = 'Producción';

    protected static ?string $navigationIcon = 'heroicon-o-cube';

    protected static ?int $navigationSort = 3;

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('activo', true)->count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('proyecto_id')
                    ->label('Proyecto')
                    ->relationship('proyecto', 'nombre')
                    ->getOptionLabelFromRecordUsing(fn ($record) => $record->nombre . ' (' . $record->cliente->nombre . ')')
                    ->searchable()
                    ->preload()
                    ->required()
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('nombre')
                    ->label('Nombre del programa')
                    ->required()
                    ->columnSpanFull(),
                Forms\Components\Select::make('responsable_inicial_id')
                    ->label('Responsable')
                    ->options(function () {
                        return User::all()->pluck('name', 'id');
                    })
                    ->default(function () {
                        // Buscar el primer usuario con rol "Ingenieria"
                        $ingenieriaUser = User::role('Ingenieria')->first();
                        return $ingenieriaUser?->id;
                    })
                    ->searchable()
                    ->required()
                    ->helperText('Por defecto se asigna a Ingeniería')
                    ->columnSpanFull(),
                Forms\Components\Textarea::make('descripcion')
                    ->label('Descripción')
                    ->rows(3)
                    ->columnSpanFull(),

                Forms\Components\Section::make('⚙️ Configuración de Fases')
                    ->description('Selecciona las fases que aplicarán para este programa. Si no seleccionas ninguna, se usarán todas las fases por defecto.')
                    ->schema([
                        Forms\Components\CheckboxList::make('fases_configuradas')
                            ->label('Fases que aplican')
                            ->options(fn () => Fase::orderBy('orden')->pluck('nombre', 'id'))
                            ->default(fn () => Fase::orderBy('orden')->pluck('id')->toArray())
                            ->columns(3)
                            ->gridDirection('row')
                            ->bulkToggleable()
                            ->helperText('Marca las fases que aplicarán para este programa. El orden se respeta según la configuración general de fases.')
                            ->columnSpanFull(),
                    ])
                    ->collapsible()
                    ->collapsed(false),

                Forms\Components\Textarea::make('notas')
                    ->label('Notas')
                    ->rows(3)
                    ->columnSpanFull(),
                Forms\Components\Toggle::make('activo')
                    ->label('Activo')
                    ->default(true)
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->striped()
            ->defaultSort('nombre', 'asc')
            ->defaultPaginationPageOption(50)
            ->columns([
                Tables\Columns\TextColumn::make('nombre')
                    ->label('Nombre')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('proyecto.nombre')
                    ->label('Proyecto')
                    ->formatStateUsing(fn ($record) => $record->proyecto->nombre . ' (' . $record->proyecto->cliente->nombre . ')')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\IconColumn::make('activo')->label('Activo')->boolean(),
                Tables\Columns\TextColumn::make('descripcion')->limit(30),
                Tables\Columns\TextColumn::make('notas')->limit(30),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('activo')
                    ->label('Estado')
                    ->boolean(),
            ]);
    }

    public static function canViewReports(): bool
    {
        $user = auth()->user();

        if (!$user) {
            return false;
        }

        $policy = app(\App\Policies\ProgramaPolicy::class);

        return $policy->viewReports($user);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProgramas::route('/'),
            'create' => Pages\CreatePrograma::route('/create'),
            'edit' => Pages\EditPrograma::route('/{record}/edit'),
            'reportes' => Pages\ReportesProgramas::route('/reportes'),
        ];
    }
}
