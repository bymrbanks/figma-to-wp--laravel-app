<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ComponentResource\Pages;
use App\Models\Component;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Select;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Resources\Resource;
use Illuminate\Support\Str;

class ComponentResource extends Resource
{
    protected static ?string $model = Component::class;

    protected static ?string $navigationIcon = 'heroicon-o-archive-box';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255), // Component name
                TextInput::make('slug')
                    ->required()
                    ->dehydrated(fn($state) => $state !== null) // Ensure slug is set based on name
                    ->default(fn($get) => Str::slug($get('name'))), // Automatically generate slug from name
                Select::make('category')
                    ->required()
                    ->searchable()
                    ->options([
                        'hero' => 'Hero',
                        'footer' => 'Footer',
                        'sidebar' => 'Sidebar',
                        'header' => 'Header',
                        'content' => 'Content',
                        'form' => 'Form',
                        'button' => 'Button',
                        'image' => 'Image',
                        'video' => 'Video',
                        'audio' => 'Audio',
                        'other' => 'Other',
                    ])
                    ->default('other'),
                Textarea::make('description')
                    ->required(),
                Textarea::make('schema')
                    ->required(), // JSON schema
                Textarea::make('fields')
                    ->required(), // JSON fields
                TextInput::make('author')
                    ->maxLength(255), // Author name
                TextInput::make('image')
                    ->maxLength(255), // Image
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')->sortable(), // Component ID
                TextColumn::make('slug')->searchable()->sortable(), // Component slug
                TextColumn::make('name')->searchable()->sortable(), // Component name
                TextColumn::make('category')->searchable()->sortable(), // Component category
                TextColumn::make('author')->searchable()->sortable(), // Author name
                // TextColumn::make('created_at')->dateTime()->sortable(), // Creation date
                // TextColumn::make('updated_at')->dateTime()->sortable(), // Last update date
            ])
            ->filters([
                // Add any necessary filters here
            ])
            ->actions([
                Tables\Actions\EditAction::make(), // Edit action
                Tables\Actions\DeleteAction::make(), // Delete action
                Tables\Actions\ViewAction::make(), // View action
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(), // Bulk delete action
            ]);
    }

    public static function getRelations(): array
    {
        return [
            // Define any relationships here
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListComponents::route('/'), // List Components page
            'create' => Pages\CreateComponent::route('/create'), // Create Component page
            'edit' => Pages\EditComponent::route('/{record}/edit'), // Edit Component page
            'view' => Pages\ViewComponent::route('/{record}'), // View Component page
        ];
    }
}
