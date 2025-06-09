<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Filament\Resources\ProductResource\RelationManagers;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cache;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-squares-2x2';

    protected static ?int $recordsPerPage = 10;

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery()->with(['category', 'brand']);

        // Cache the results instead of the query builder
        if ($cachedResults = cache()->get('products_list')) {
            return Product::query()->whereIn('id', $cachedResults->pluck('id'));
        }

        $results = $query->get();
        cache()->put('products_list', $results, now()->addHour());

        return Product::query()->whereIn('id', $results->pluck('id'));
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Group::make()->schema([
                    Section::make("Product Information")->schema([
                        TextInput::make("name")
                            ->required()
                            ->maxLength(255)
                            ->placeholder("Enter product name")
                            ->live(onBlur: true)
                            ->afterStateUpdated(
                                fn($state, Set $set) =>
                                $set('slug', Str::slug($state))
                            ),
                        TextInput::make("slug")
                            ->required()
                            ->maxLength(255)
                            ->disabled()
                            ->dehydrated()
                            ->unique(Product::class, 'slug', ignoreRecord: true),
                        MarkdownEditor::make("description")
                            ->columnSpanFull()
                            ->fileAttachmentsDirectory('products')
                            ->placeholder("Enter product description"),
                    ])->columns(2),
                    Section::make("Images")->schema([
                        FileUpload::make('images')
                            ->maxFiles(5)
                            ->multiple()
                            ->directory('products')
                            ->reorderable()
                            ->imagePreviewHeight('100')
                            ->loadingIndicatorPosition('left')
                            ->panelAspectRatio('2:1')
                            ->panelLayout('integrated')
                            ->removeUploadedFileButtonPosition('right')
                            ->uploadProgressIndicatorPosition('left'),
                    ]),
                ])->columnSpan(2),

                Group::make()->schema([
                    Section::make("Price")->schema([
                        TextInput::make("price")
                            ->required()
                            ->numeric()
                            ->prefix('$')
                            ->placeholder("Enter product price"),
                    ]),

                    Section::make("Associations")->schema([
                        Select::make("category_id")
                            ->required()
                            ->searchable()
                            ->preload()
                            ->relationship('category', 'name')
                            ->createOptionForm([
                                TextInput::make('name')
                                    ->required(),
                                TextInput::make('slug')
                                    ->required(),
                            ]),
                        Select::make("brand_id")
                            ->required()
                            ->searchable()
                            ->preload()
                            ->relationship('brand', 'name')
                            ->createOptionForm([
                                TextInput::make('name')
                                    ->required(),
                                TextInput::make('slug')
                                    ->required(),
                            ]),
                    ]),
                    Section::make("Status")->schema([
                        Grid::make()->columns(
                            [
                                'default' => 2,
                            ]
                        )->schema([
                            Toggle::make("in_stock")
                                ->required()
                                ->default(true),
                            Toggle::make("is_active")
                                ->required()
                                ->default(true),
                            Toggle::make("is_featured")
                                ->required(),
                            Toggle::make("on_sale")
                        ])
                    ]),
                ])->columnSpan(1)
            ])->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultPaginationPageOption(10)
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->wrap(),
                Tables\Columns\TextColumn::make('category.name')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('brand.name')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('price')
                    ->money()
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->boolean()
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_featured')
                    ->boolean()
                    ->sortable(),
                Tables\Columns\IconColumn::make('in_stock')
                    ->boolean()
                    ->sortable(),
                Tables\Columns\IconColumn::make('on_sale')
                    ->boolean()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('category')
                    ->relationship('category', 'name')
                    ->preload()
                    ->multiple(),
                SelectFilter::make('brand')
                    ->relationship('brand', 'name')
                    ->preload()
                    ->multiple(),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\DeleteAction::make(),
                ])
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->poll('60s'); // Refresh data every 60 seconds
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
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }
}
