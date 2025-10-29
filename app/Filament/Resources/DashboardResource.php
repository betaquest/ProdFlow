<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DashboardResource\Pages;
use App\Models\Dashboard;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class DashboardResource extends Resource
{
    protected static ?string $model = Dashboard::class;

    protected static ?string $navigationIcon = 'heroicon-o-tv';

    protected static ?string $navigationLabel = 'Dashboards';

    protected static ?string $navigationGroup = 'Configuración';

    // protected static ?string $navigationIcon = 'heroicon-o-chart-bar';
    protected static ?int $navigationSort = 2;

    // Restringir acceso solo a Administradores
    public static function canViewAny(): bool
    {
        return auth()->user()?->hasRole('Administrador') ?? false;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Información General')
                    ->schema([
                        Forms\Components\TextInput::make('nombre')
                            ->label('Nombre del Dashboard')
                            ->required()
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('slug')
                            ->label('Identificador (URL)')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->prefix('/dashboards/')
                            ->columnSpanFull(),
                        Forms\Components\Textarea::make('descripcion')
                            ->rows(3)
                            ->columnSpanFull(),
                        Forms\Components\Toggle::make('activo')
                            ->default(true)
                            ->inline(false),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Opciones de Visualización')
                    ->description('Configura qué elementos se mostrarán en el dashboard')
                    ->schema([
                        Forms\Components\Toggle::make('mostrar_logotipo')
                            ->label('Mostrar Logotipo')
                            ->default(true)
                            ->inline(false)
                            ->helperText('Muestra el logotipo de la empresa en el dashboard'),

                        Forms\Components\Toggle::make('mostrar_titulo')
                            ->label('Mostrar Título')
                            ->default(true)
                            ->inline(false)
                            ->helperText('Muestra el nombre del dashboard en el encabezado'),

                        Forms\Components\Toggle::make('mostrar_reloj')
                            ->label('Mostrar Reloj')
                            ->default(true)
                            ->inline(false)
                            ->helperText('Muestra un reloj en tiempo real'),

                        Forms\Components\Toggle::make('mostrar_estadisticas')
                            ->label('Mostrar Estadísticas')
                            ->default(true)
                            ->inline(false)
                            ->live()
                            ->helperText('Muestra la barra completa de estadísticas (contadores + barra de progreso)'),

                        Forms\Components\Toggle::make('mostrar_barra_progreso')
                            ->label('Mostrar Barra de Progreso')
                            ->default(true)
                            ->inline(false)
                            ->hidden(fn ($get) => !$get('mostrar_estadisticas'))
                            ->helperText('Muestra la barra de progreso visual (solo aplica si las estadísticas están activas)'),

                        Forms\Components\ColorPicker::make('color_fondo')
                            ->label('Color de Fondo')
                            ->helperText('Deja vacío para usar el color por defecto')
                            ->columnSpanFull(),
                    ])
                    ->columns(2)
                    ->collapsible(),

                Forms\Components\Section::make('Filtros de Visualización')
                    ->description('Selecciona qué datos mostrar en el dashboard')
                    ->schema([
                        // FILTRO DE CLIENTES
                        Forms\Components\Grid::make(1)
                            ->schema([
                                Forms\Components\Toggle::make('todos_clientes')
                                    ->label('📋 Mostrar Todos los Clientes')
                                    ->default(true)
                                    ->inline(false)
                                    ->live()
                                    ->helperText('Activado = Todos los clientes | Desactivado = Selecciona específicos'),

                                Forms\Components\CheckboxList::make('clientes_ids')
                                    ->label('Selecciona Clientes')
                                    ->options(fn () => \App\Models\Cliente::orderBy('nombre')->pluck('nombre', 'id'))
                                    ->columns(3)
                                    ->gridDirection('row')
                                    ->bulkToggleable()
                                    ->searchable()
                                    ->hidden(fn ($get) => $get('todos_clientes'))
                                    ->helperText('Marca los clientes que quieres incluir'),
                            ]),

                        Forms\Components\Placeholder::make('divider1')
                            ->content('───────────────────────────────────────')
                            ->columnSpanFull(),

                        // FILTRO DE FASES
                        Forms\Components\Grid::make(1)
                            ->schema([
                                Forms\Components\Toggle::make('todas_fases')
                                    ->label('📊 Mostrar Todas las Fases')
                                    ->default(true)
                                    ->inline(false)
                                    ->live()
                                    ->helperText('Activado = Todas las fases | Desactivado = Selecciona específicas'),

                                Forms\Components\CheckboxList::make('fases_ids')
                                    ->label('Selecciona Fases')
                                    ->options(fn () => \App\Models\Fase::orderBy('orden')->pluck('nombre', 'id'))
                                    ->columns(4)
                                    ->gridDirection('row')
                                    ->bulkToggleable()
                                    ->hidden(fn ($get) => $get('todas_fases'))
                                    ->helperText('Marca las fases que quieres mostrar en el dashboard'),
                            ]),
                    ])
                    ->columns(1)
                    ->collapsible(),

                Forms\Components\Section::make('Filtros de Programas')
                    ->description('Configura qué programas mostrar y cómo ordenarlos')
                    ->schema([
                        Forms\Components\Toggle::make('mostrar_solo_en_proceso')
                            ->label('📌 Mostrar Solo Programas en Proceso')
                            ->default(false)
                            ->inline(false)
                            ->helperText('Activado = Solo programas con al menos una fase en progreso | Desactivado = Todos los programas'),

                        Forms\Components\Toggle::make('ocultar_finalizados_antiguos')
                            ->label('🗓️ Ocultar Programas Finalizados Antiguos')
                            ->default(false)
                            ->inline(false)
                            ->helperText('Activado = Solo muestra finalizados del día actual | Desactivado = Muestra todos los finalizados'),

                        Forms\Components\Toggle::make('ocultar_completamente_finalizados')
                            ->label('✅ Ocultar Programas Completamente Finalizados')
                            ->default(false)
                            ->inline(false)
                            ->helperText('Activado = Oculta programas con todas sus fases completadas | Desactivado = Muestra todos (se marcarán en verde)'),

                        Forms\Components\Toggle::make('usar_alias_fases')
                            ->label('📝 Usar Alias de Fases')
                            ->default(false)
                            ->inline(false)
                            ->helperText('Activado = Muestra el alias corto de las fases | Desactivado = Muestra el nombre completo'),

                        Forms\Components\Select::make('orden_programas')
                            ->label('🔢 Orden de Programas')
                            ->options([
                                'nombre' => 'Por Nombre del Programa',
                                'cliente' => 'Por Cliente',
                                'proyecto' => 'Por Proyecto',
                            ])
                            ->default('nombre')
                            ->required()
                            ->helperText('Define el orden en que aparecen los programas en el dashboard'),
                    ])
                    ->columns(1)
                    ->collapsible(),

                Forms\Components\Section::make('⚠️ Alertas de Antigüedad')
                    ->description('Resalta visualmente programas que llevan demasiado tiempo abiertos sin finalizar')
                    ->schema([
                        Forms\Components\Toggle::make('alerta_antiguedad_activa')
                            ->label('🚨 Activar Alerta por Antigüedad')
                            ->default(false)
                            ->inline(false)
                            ->live()
                            ->helperText('Marca de rojo las filas de programas que llevan muchos días sin finalizar'),

                        Forms\Components\TextInput::make('alerta_antiguedad_dias')
                            ->label('⏰ Días para Alerta')
                            ->numeric()
                            ->default(7)
                            ->minValue(1)
                            ->maxValue(365)
                            ->suffix('días')
                            ->hidden(fn ($get) => !$get('alerta_antiguedad_activa'))
                            ->helperText('Los programas con más de esta cantidad de días abiertos se marcarán de rojo'),
                    ])
                    ->columns(1)
                    ->collapsible()
                    ->collapsed(),

                Forms\Components\Section::make('Configuración Adicional')
                    ->schema([
                        Forms\Components\TextInput::make('tiempo_actualizacion')
                            ->numeric()
                            ->label('⏱️ Tiempo de actualización (segundos)')
                            ->default(30)
                            ->minValue(5)
                            ->suffix('seg')
                            ->helperText('Define cada cuántos segundos se actualiza la pantalla automáticamente'),
                    ])
                    ->columns(1)
                    ->collapsed(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->striped()
            ->defaultSort('nombre', 'asc')
            ->defaultPaginationPageOption(50)
            ->columns([
                Tables\Columns\TextColumn::make('nombre')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('slug')
                    ->label('URL')
                    ->formatStateUsing(fn ($state) => "/dashboards/{$state}")
                    ->searchable(),
                Tables\Columns\IconColumn::make('activo')->boolean()->label('Activo'),
                Tables\Columns\IconColumn::make('mostrar_logotipo')
                    ->boolean()
                    ->label('Logo')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\IconColumn::make('mostrar_reloj')
                    ->boolean()
                    ->label('Reloj')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\IconColumn::make('mostrar_estadisticas')
                    ->boolean()
                    ->label('Estadísticas')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\IconColumn::make('mostrar_barra_progreso')
                    ->boolean()
                    ->label('Barra Progreso')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\ColorColumn::make('color_fondo')
                    ->label('Color')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('descripcion')
                    ->limit(30)
                    ->toggleable(),
                Tables\Columns\TextColumn::make('tiempo_actualizacion')
                    ->label('Actualización')
                    ->sortable()
                    ->alignCenter()
                    ->color('gray')
                    ->formatStateUsing(fn ($state) => $state.' seg'),
                Tables\Columns\IconColumn::make('mostrar_solo_en_proceso')
                    ->boolean()
                    ->label('Solo En Proceso')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\IconColumn::make('ocultar_finalizados_antiguos')
                    ->boolean()
                    ->label('Ocultar Finalizados')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('orden_programas')
                    ->label('Orden')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\Action::make('ver')
                        ->label('Ver Dashboard')
                        ->icon('heroicon-o-eye')
                        ->url(fn ($record) => url("/dashboards/{$record->slug}"))
                        ->openUrlInNewTab(),
                    Tables\Actions\Action::make('copiar')
                        ->label('Copiar link')
                        ->icon('heroicon-o-link')
                        ->action(function ($record, $livewire) {
                            $url = url("/dashboards/{$record->slug}");
                            $livewire->js("navigator.clipboard.writeText('{$url}'); alert('Enlace copiado: {$url}');");
                        }),

                ]),

            ]);
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
            'index' => Pages\ListDashboards::route('/'),
            'create' => Pages\CreateDashboard::route('/create'),
            'edit' => Pages\EditDashboard::route('/{record}/edit'),
        ];
    }
}
