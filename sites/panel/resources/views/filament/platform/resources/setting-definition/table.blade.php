<div class="px-4 space-y-4" 
    x-data="{ 
        activeModule: $persist(null).as('settings-active-module'),
        activeSection: $persist(null).as('settings-active-section'),
        
        toggleModule(id) {
            this.activeModule = (this.activeModule === id) ? null : id;
            if (this.activeModule === null) this.activeSection = null;
        },
        
        toggleSection(id) {
            this.activeSection = (this.activeSection === id) ? null : id;
        }
    }"
>
    @php
        $records = $this->getTable()->getRecords();
        $moduleGroups = $records->groupBy('module_id');
    @endphp

    @foreach ($moduleGroups as $moduleId => $moduleSettings)
        @php
            $firstRecord = $moduleSettings->first();
            $module = $firstRecord->module()->getResults();
            $moduleName = is_object($module) ? $module->name : (is_string($firstRecord->module) ? $firstRecord->module : 'Без модуля');
            $moduleIdKey = $moduleId ?? 'none';
        @endphp
        
        <div class="fi-section rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10 overflow-hidden"
             :class="activeModule === '{{ $moduleIdKey }}' ? 'ring-primary-500' : ''"
        >
            <div 
                @click="toggleModule('{{ $moduleIdKey }}')"
                class="fi-section-header flex cursor-pointer items-center justify-between gap-x-3 bg-gray-50 px-6 py-4 dark:bg-white/5 transition hover:bg-gray-100 dark:hover:bg-white/10"
            >
                <div class="flex items-center gap-x-3">
                    <div class="flex items-center justify-center w-8 h-8 rounded-lg bg-white dark:bg-gray-800 shadow-sm ring-1 ring-gray-950/5">
                        <x-heroicon-o-puzzle-piece class="w-5 h-5 text-gray-500" />
                    </div>
                    <div>
                        <h2 class="text-base font-semibold leading-6 text-gray-950 dark:text-white">
                            {{ $moduleName }}
                            @if(!is_object($module) && $moduleId) <span class="text-xs text-gray-400 font-normal ml-2">(ID: {{ $moduleId }})</span> @endif
                        </h2>
                    </div>
                    <span class="inline-flex items-center rounded-md bg-white px-2 py-0.5 text-xs font-medium text-gray-600 ring-1 ring-inset ring-gray-950/10 dark:bg-gray-800 dark:text-gray-400">
                        {{ $moduleSettings->count() }}
                    </span>
                </div>
                
                <div class="flex items-center gap-2">
                    <x-heroicon-m-chevron-up x-show="activeModule === '{{ $moduleIdKey }}'" x-cloak class="h-5 w-5 text-gray-400" />
                    <x-heroicon-m-chevron-down x-show="activeModule !== '{{ $moduleIdKey }}'" x-cloak class="h-5 w-5 text-gray-400" />
                </div>
            </div>

            <div x-show="activeModule === '{{ $moduleIdKey }}'" x-collapse class="fi-section-content border-t border-gray-100 dark:border-white/5 p-4 bg-gray-50/30 dark:bg-gray-900/50">
                <div class="space-y-4">
                    @foreach ($moduleSettings->groupBy('section_id') as $sectionId => $sectionSettings)
                        @php
                            $firstSectionRecord = $sectionSettings->first();
                            $section = $firstSectionRecord->section()->getResults();
                            $sectionName = is_object($section) ? $section->name : 'Общие';
                            $sectionIdKey = ($sectionId ?? 'global') . '-' . $moduleIdKey;
                        @endphp
                        
                        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm ring-1 ring-gray-950/5 dark:ring-white/10 overflow-hidden">
                            <div 
                                @click="toggleSection('{{ $sectionIdKey }}')"
                                class="flex items-center justify-between px-4 py-3 cursor-pointer hover:bg-gray-50 dark:hover:bg-white/5 transition"
                            >
                                <div class="flex items-center gap-x-3">
                                    <div class="w-1.5 h-6 rounded-full" :class="activeSection === '{{ $sectionIdKey }}' ? 'bg-primary-500' : 'bg-gray-200 dark:bg-gray-700'"></div>
                                    <h3 class="text-sm font-bold text-gray-700 dark:text-gray-300">
                                        {{ $sectionName }}
                                    </h3>
                                    <span class="text-[10px] px-1.5 py-0.5 rounded-full bg-gray-100 dark:bg-gray-700 text-gray-500">
                                        {{ $sectionSettings->count() }}
                                    </span>
                                </div>
                                <div class="ml-auto flex items-center gap-2">
                                     <x-heroicon-m-chevron-down 
                                        class="h-4 w-4 text-gray-400 transition-transform duration-200"
                                         x-bind:style="activeSection === '{{ $sectionIdKey }}' ? 'transform: rotate(180deg)' : ''"
                                     />
                                </div>
                            </div>
                            
                            <div x-show="activeSection === '{{ $sectionIdKey }}'" x-collapse>
                                <div class="p-4 space-y-3 border-t border-gray-50 dark:border-white/5 bg-gray-50/10">
                                    @foreach ($sectionSettings as $record)
                                        <div class="group flex items-center justify-between gap-4 rounded-xl border border-gray-200 bg-white p-4 shadow-sm transition hover:border-primary-500 hover:shadow-md dark:border-white/10 dark:bg-gray-900 dark:hover:border-primary-500/50">
                                            
                                            <!-- Left: Info -->
                                            <div class="flex-1 min-w-0 grid gap-1">
                                                <div class="flex items-center gap-2 flex-wrap">
                                                    <h4 class="text-sm font-semibold text-gray-950 dark:text-white truncate">
                                                        {{ $record->name }}
                                                    </h4>
                                                    
                                                    @if($record->is_system)
                                                        <button 
                                                            wire:click="mountTableAction('toggleSystem', {{ $record->id }})"
                                                            class="inline-flex items-center gap-1 rounded-md bg-red-50 px-1.5 py-0.5 text-[10px] font-medium text-red-700 ring-1 ring-inset ring-red-600/10 dark:bg-red-400/10 dark:text-red-400 hover:bg-red-100 transition cursor-pointer"
                                                            title="Нажмите, чтобы изменить системный статус"
                                                        >
                                                            <x-heroicon-m-lock-closed class="w-3 h-3" />
                                                            Система
                                                        </button>
                                                    @endif

                                                    @if($record->is_global)
                                                        <span class="inline-flex items-center gap-1 rounded-md bg-green-50 px-1.5 py-0.5 text-[10px] font-medium text-green-700 ring-1 ring-inset ring-green-600/10 dark:bg-green-400/10 dark:text-green-400">
                                                            <x-heroicon-m-globe-alt class="w-3 h-3" />
                                                            Глобал
                                                        </span>
                                                    @endif

                                                    @if($record->is_public)
                                                        <button 
                                                            wire:click="mountTableAction('togglePublic', {{ $record->id }})"
                                                            class="inline-flex items-center gap-1 rounded-md bg-blue-50 px-1.5 py-0.5 text-[10px] font-medium text-blue-700 ring-1 ring-inset ring-blue-600/10 dark:bg-blue-400/10 dark:text-blue-400 hover:bg-blue-100 transition cursor-pointer"
                                                            title="Нажмите, чтобы скрыть из панели клиента"
                                                        >
                                                            <x-heroicon-m-eye class="w-3 h-3" />
                                                            Public
                                                        </button>
                                                    @else
                                                        <button 
                                                            wire:click="mountTableAction('togglePublic', {{ $record->id }})"
                                                            class="inline-flex items-center gap-1 rounded-md bg-gray-50 px-1.5 py-0.5 text-[10px] font-medium text-gray-500 ring-1 ring-inset ring-gray-600/10 dark:bg-gray-400/10 dark:text-gray-400 hover:bg-gray-100 transition cursor-pointer"
                                                            title="Нажмите, чтобы показать в панели клиента"
                                                        >
                                                            <x-heroicon-m-eye-slash class="w-3 h-3" />
                                                            Private
                                                        </button>
                                                    @endif
                                                </div>
                                                
                                                <div class="text-[11px] text-gray-400 font-mono dark:text-gray-500 truncate flex items-center gap-1">
                                                    <span class="select-all">{{ $record->key }}</span>
                                                    <span class="text-gray-300 dark:text-gray-700">•</span>
                                                    <span class="uppercase tracking-widest">{{ $record->type }}</span>
                                                </div>
                                            </div>

                                            <!-- Right: Actions -->
                                            <div class="flex items-center gap-1 shrink-0 bg-gray-50 dark:bg-white/5 p-1 rounded-lg transition-opacity">
                                                
                                                <x-filament::icon-button
                                                    :icon="$record->is_public ? 'heroicon-o-eye' : 'heroicon-o-eye-slash'"
                                                    :color="$record->is_public ? 'success' : 'gray'"
                                                    wire:click="mountTableAction('togglePublic', {{ $record->id }})"
                                                    size="sm"
                                                    tooltip="Показать/Скрыть"
                                                />

                                                <x-filament::icon-button
                                                    icon="heroicon-m-pencil-square"
                                                    color="gray"
                                                    wire:click="mountTableAction('edit', {{ $record->id }})"
                                                    size="sm"
                                                    tooltip="Редактировать"
                                                />

                                                @if(!$record->is_system)
                                                    <x-filament::icon-button
                                                        icon="heroicon-m-trash"
                                                        color="danger"
                                                        wire:click="mountTableAction('delete', {{ $record->id }})"
                                                        size="sm"
                                                        tooltip="Удалить"
                                                    />
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endforeach
    
    @if($records->isEmpty())
        <div class="flex flex-col items-center justify-center py-24 text-center">
            <div class="rounded-full bg-gray-100 p-6 dark:bg-gray-800">
                <x-heroicon-o-magnifying-glass class="h-10 w-10 text-gray-300 dark:text-gray-600" />
            </div>
            <h3 class="mt-4 text-lg font-bold text-gray-900 dark:text-white">Настройки не найдены</h3>
            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400 max-w-xs mx-auto">
                Попробуйте изменить параметры фильтрации или создайте новую настройку.
            </p>
        </div>
    @endif
</div>
