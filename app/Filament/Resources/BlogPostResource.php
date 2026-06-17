<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BlogPostResource\Pages;
use App\Models\BlogPost;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class BlogPostResource extends Resource
{
    protected static ?string $model = BlogPost::class;

    protected static ?string $navigationIcon = 'heroicon-o-newspaper';
    protected static ?string $navigationGroup = 'Site web';
    protected static ?string $navigationLabel = 'Blog';
    protected static ?string $modelLabel = 'article';
    protected static ?string $pluralModelLabel = 'Blog';
    protected static ?int $navigationSort = 6;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Article')->columns(2)->schema([
                Forms\Components\TextInput::make('title')->label('Titre')->required()->live(onBlur: true)
                    ->afterStateUpdated(fn ($state, Forms\Set $set) => $set('slug', Str::slug($state)))->columnSpanFull(),
                Forms\Components\TextInput::make('slug')->label('Identifiant URL')->required()->unique(ignoreRecord: true),
                Forms\Components\TextInput::make('category')->label('Catégorie')->placeholder('Conseils, Actualités…'),
                Forms\Components\Textarea::make('excerpt')->label('Résumé')->rows(2)->columnSpanFull()
                    ->helperText('Court texte affiché dans la liste des articles.'),
                Forms\Components\FileUpload::make('featured_image')->label('Image à la une')->image()->directory('blog')->columnSpanFull(),
                Forms\Components\RichEditor::make('content')->label('Contenu')->required()->columnSpanFull(),
                Forms\Components\TagsInput::make('tags')->label('Mots-clés')->columnSpanFull(),
            ]),
            Forms\Components\Section::make('Publication')->columns(2)->schema([
                Forms\Components\Toggle::make('is_published')->label('Publié'),
                Forms\Components\Toggle::make('is_featured')->label('À la une'),
                Forms\Components\DateTimePicker::make('published_at')->label('Date de publication')
                    ->helperText('Laisser vide = maintenant à la publication.'),
            ]),
            Forms\Components\Section::make('SEO')->columns(2)->collapsed()->schema([
                Forms\Components\TextInput::make('meta_title')->label('Titre SEO')->maxLength(70),
                Forms\Components\TextInput::make('meta_description')->label('Meta description')->maxLength(180),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\ImageColumn::make('featured_image')->label('Image'),
                Tables\Columns\TextColumn::make('title')->label('Titre')->searchable()->limit(50),
                Tables\Columns\TextColumn::make('category')->label('Catégorie')->badge(),
                Tables\Columns\IconColumn::make('is_published')->label('Publié')->boolean(),
                Tables\Columns\IconColumn::make('is_featured')->label('À la une')->boolean(),
                Tables\Columns\TextColumn::make('views_count')->label('Vues')->sortable(),
            ])
            ->actions([Tables\Actions\EditAction::make(), Tables\Actions\DeleteAction::make()])
            ->bulkActions([Tables\Actions\BulkActionGroup::make([Tables\Actions\DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBlogPosts::route('/'),
            'create' => Pages\CreateBlogPost::route('/create'),
            'edit' => Pages\EditBlogPost::route('/{record}/edit'),
        ];
    }
}
