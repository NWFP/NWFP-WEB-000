<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PageResource\Pages;
use App\Filament\Resources\PageResource\RelationManagers;
use App\Models\Page;
use Filament\Forms;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Radio;
use Filament\Forms\Form;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\Grid;

use Filament\Resources\Resource;

use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Table;
use Filament\Tables\Enums\ActionsPosition;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Contracts\View\View;

class PageResource extends Resource
{
    protected static ?string $model = Page::class;
    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')->required()
                ->maxLength(50)->label('Blade name'),
                TextInput::make('imagefile')->label('Image filename (incl. ext)')
                ->maxLength(80),
                TextInput::make('title')->required()
                ->maxLength(255),
                TextInput::make('description')->required()
                ->maxLength(255),
                Select::make('status')
                ->options([
                    'Draft' => 'Draft',
                    'Reviewing' => 'Reviewing',
                    'Published' => 'Published',
                ]),
                Select::make('tags')
                ->relationship('tags', 'name')
                ->preload()
                ->multiple(),
                //TextInput::make('rank')->numeric()->default(0)->label('Rank (for page display priority)'),//
                //Radio::make('status')->options(['draft' => 'Draft','scheduled' => 'Scheduled','published' => 'Published']) -> descriptions(['draft' => 'Is not visible.','scheduled' => 'Will be visible.','published' => 'Is visible.']),
                Toggle::make('is_focus')->label('On home page'),
                Toggle::make('is_pinned')->label('On top'),
                Radio::make('rank_focus')->options(['1' => 1,'2' => 2,'3' =>3,'4' =>4,'5' =>5,'6' =>6,'7' =>7,'8' =>8,'9' =>9,'10' =>10]) 
                ->default(null) -> descriptions(['1' => '','scheduled' => 'Will be visible.','published' => 'Is visible.'])->label('Rank (on Home Page)'),
                Radio::make('rank_pinned')->options(['1' => 1,'2' => 2,'3' =>3,'4' =>4,'5' =>5]) 
                ->default(null) -> descriptions(['1' => '','scheduled' => 'Will be visible.','published' => 'Is visible.'])->label('Rank (on Top)'),
                // the fileupload will not work if the disk is on the public directory. When the storage place is available, we can revisit that!
                //FileUpload::make('imagefile')
                  //   ->disk('images'),
                ]);
    }

    public static function table(Table $table): Table
    {
        return $table
                ->columns([
                    TextColumn::make('title')->wrap(80)->sortable(),
                    IconColumn::make('status')
                        ->icon(fn (string $state): string => match ($state) {
                        'Draft' => 'heroicon-o-pencil',
                        'Reviewing' => 'heroicon-o-clock',
                        'Published' => 'heroicon-o-check-badge',
                        })
                        ->color(fn (string $state): string => match ($state) {
                            'Draft' => 'info',
                            'Reviewing' => 'warning',
                            'Published' => 'success',
                            default => 'gray',
                        }),
                    IconColumn::make('is_focus')
                        ->boolean()
                        ->trueIcon('heroicon-o-check-badge')
                        ->falseIcon('heroicon-o-x-mark')
                        ->label('on Home Page')->sortable(),
                    TextColumn::make('rank_focus')->numeric()->sortable()
                        ->label('Rank (on Home Page)'),   
                    IconColumn::make('is_pinned')
                        ->boolean()
                        ->trueIcon('heroicon-m-bookmark-square')
                        ->falseIcon('heroicon-o-x-mark')
                        ->label('on Top')->sortable(),
                        TextColumn::make('rank_pinned')->numeric()->sortable()
                        ->label('Rank (on Top)'),
                    TextColumn::make('name')->label('Filename')->sortable(),
                    #TextColumn::make('full_url')->wrap(50)->label('View Page')->html(),
                    TextColumn::make('tags.name'),
                    TextColumn::make('imagefile')->label('Image filename (inc ext)'),
                    TextColumn::make('description')->label('Summary')->wrap(100),
        ])
        ->defaultSort('title', 'asc')
            ->filters([
                Filter::make('is_focus')
    ->query(fn (Builder $query): Builder => $query->where('is_focus', true))->toggle(),
                Filter::make('is_pinned')->toggle(),
                SelectFilter::make('status')->multiple()
                     ->options([
                    'Draft' => 'Draft',
                      'Reviewing' => 'Reviewing',
                      'Published' => 'Published',
    ])
            ])
            ->actions([

                Tables\Actions\EditAction::make()->button(),
                Tables\Actions\ViewAction::make()->button(),
            ], position: ActionsPosition::BeforeColumns)

            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }



  #This is for the view, the infolist a - a readonly version
    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Grid::make(1)->schema([
                    TextEntry::make('title')->label(''),
                    TextEntry::make('description')->label(''),
                    TextEntry::make('full_URL')->label('Go to Page')->html()->color('success'),
                ])
            ]);
    }


    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPages::route('/'),
            'create' => Pages\CreatePage::route('/create'),
            'edit' => Pages\EditPage::route('/{record}/edit'),
            'view'=> Pages\ViewPage::route('/{record}'),
        ];
    }
}
