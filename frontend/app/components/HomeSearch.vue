<template>
    <div class="w-full mx-auto mt-8">
        <form class="bg-white shadow-2xl p-6 flex flex-col md:flex-row items-center gap-2 rounded-md">
            <SearchIndexFilter />
            <SearchGameFilter />
            <SearchRadiusFilter />
            <SearchCityFilter />

            <div class="flex-1 w-full px-3 py-2 hidden md:block">
                <button
                    type="submit"
                    class="text-white bg-primary w-full rounded-4xl py-2 px-4 transition-all hover:opacity-90 hover:cursor-pointer"
                >{{ $t('search.submit') }}</button>
            </div>
        </form>
    </div>
</template>