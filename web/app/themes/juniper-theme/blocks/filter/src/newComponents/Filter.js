import React, {useEffect, useState} from "react";
import {useMediaQuery} from "react-responsive";
import MobileFilter from "./SingleFilterComponents/MobileFilter";
import {
    filterOptionToElement,
    loadInPostsFromPage, postApplysToTax, postInSelection,
    renderPost,
    rerenderSlick
} from "../utils";
import {PlusButtonIcon} from "./Icons";
import FilterTextSearch from "./SingleFilterComponents/FilterTextSearch";
import FilterCheckbox from "./SingleFilterComponents/FilterCheckbox";

const FilterNew = (data) => {
    const title = data.title ?? '';

    const postType = data.postType ?? 'product'

    const resturl = data.restUrl

    // noinspection JSUnresolvedReference
    const translationObject = translation ?? {
        loading: '',
        no_results: '',
        open_filter: '',
        metals_accessories: '',
        colors: '',
        product_category: '',
        checkbox: '',
        product_search: '',
        load_more: '',
        filter_delete_button: '',
        filter_sample_available: '',
        filter_online_available: '',
    };

    const postsPerPage = 6

    const [numberOfPostsVisible, setNumberOfPostsVisible] = useState(postsPerPage);

    // filter options that get displayed
    const [filterOptions, setFilterOptions] = useState([])
    // selection of the filter (what to filter for)
    const [filterSelected, setFilterSelected] = useState({})

    const [shouldShowFilterItems, showFilterItems] = useState(true)

    // all posts that exist
    const [allPosts, setAllPosts] = useState(data.posts)
    // posts after being run through the filter
    const [filteredPosts, setFilteredPosts] = useState(data.posts)
    // posts that get displayed
    const [postsToDisplay, setPostsToDisplay] = useState(data.posts.slice(0, postsPerPage))

    const [isCurrentlyLoading, currentlyLoading] = useState(false)

    const currentlyMobile = useMediaQuery({query: '(max-width: 640px) '})

    function morePostsToDisplay() {
        return postsToDisplay.length < filteredPosts.length
    }

    function showMore() {
        const current = numberOfPostsVisible ?? 0

        const nextPosts = filteredPosts.slice(current, postsPerPage + current)

        setPostsToDisplay(postsToDisplay.concat(nextPosts))
        setNumberOfPostsVisible(postsToDisplay.length)
    }

    function loadPosts(lastAdded = 1, currentPage = 0, postsPulled = []) {
        if (lastAdded <= 0) {
            currentlyLoading(false)
            return
        }

        const nextPostsPromise = loadInPostsFromPage(resturl, postType, currentPage)

        nextPostsPromise.then((nextPosts) => {
            const postsAdded = nextPosts.length

            const allPostsPulled = postsPulled.concat(nextPosts)

            setAllPosts(allPostsPulled)

            currentPage++

            loadPosts(postsAdded, currentPage, allPostsPulled)
        })
    }

    function applyFilter(filter) {
        const filterOptions = Object.entries(filter).filter(keyValue => keyValue[1] !== '')

        let toFilterData = allPosts

        for (const filterValue of filterOptions) {
            toFilterData = toFilterData.filter((post) => postInSelection(filterValue, post))
        }

        setFilteredPosts(toFilterData)
    }

    function applyValueToFilter(filterKey, filterValue) {
        setFilterSelected((prevFilter) => ({
            ...prevFilter,
            [filterKey]: filterValue
        }))
    }

    function setUpFilters() {
        const filterOptions = data.filterOptions ?? []

        const preparedOptions = filterOptions.map((filterOption) => {
            filterOption.onChange = (selected) => {
                applyValueToFilter(filterOption.filter_choices, selected)
            }

            filterOption.url = filterOption.filter_choices.replaceAll('_', '-')

            return filterOption
        })

        // move checkboxes to the back
        preparedOptions.sort((filterOption) => {
            return filterOption.type === 'checkbox' ? 1 : -1
        })

        setFilterOptions(preparedOptions)
    }

    useEffect(() => {
        applyFilter(filterSelected)
    }, [filterSelected]);

    useEffect(() => {
        applyFilter(filterSelected)
    }, [allPosts]);

    useEffect(() => {
        setPostsToDisplay(filteredPosts.slice(0, numberOfPostsVisible))
    }, [filteredPosts]);

    useEffect(() => {
        currentlyLoading(true)

        setUpFilters()
        loadPosts()
    }, []);

    useEffect(rerenderSlick, [postsToDisplay]);

    return (
        <div className={"w-full"}>
            <div className={"container"}><h1 className={"mb-0 sm:mb-6"}>{title}</h1></div>
            <div className={"container mx-auto"}>
                {currentlyMobile ? <MobileFilter data={data}/> : null}
                {shouldShowFilterItems ?
                    <>
                        <div id={'filter-items'} className={'grid grid-cols-12 justify-start mt-6 sm:mt-0'}>
                            <FilterTextSearch
                                label={'Product Search'}
                                name={'Product Search'}
                                url={'text'}
                                placeholder={translationObject.product_search}
                                onChange={(newValue) => null
                                    // applyValueToFilter('searchText', newValue.trim().toLowerCase())
                                }
                            />
                        </div>
                        <div className={'grid grid-cols-3 gap-y-14 sm:gap-7 mt-6 sm:mt-0'}>
                            {filterOptions.map(filterOptionToElement)}
                        </div>
                        <div className="flex flex-row gap-5 col-span-12">
                            <div>
                                <FilterCheckbox
                                    key={'sampleAvailable'}
                                    name={'sampleAvailable'}
                                    label={translation.filter_sample_available}
                                    url={'purchasability'}
                                    onChange={(isChecked) => setFilterSelected(prevFilters => ({
                                        ...prevFilters,
                                        purchasability: isChecked ? 'muster-verfuegbar' : ''
                                    }))}
                                />
                            </div>
                            <div className="col-span-12 block mb-8">
                                {!data.shop && (
                                <FilterCheckbox
                                    key={'onlineAvailable'}
                                    name={'onlineAvailable'}
                                    label={translation.filter_online_available}
                                    url={'online-available'}
                                    isChecked={filterSelected.onlineAvailable}
                                    onChange={(isChecked) => setFilterSelected(prevFilters => ({
                                        ...prevFilters,
                                        onlineAvailable: isChecked
                                    }))}
                                />)}
                            </div>
                        </div>
                    </>
                    : null}
            </div>
            <div className={'container mt-5'}>
                <div className={"grid grid-cols-3 mb-10 gap-y-14 sm:gap-7 filter-grid flex flex-wrap"}>
                    {postsToDisplay.length ?
                        postsToDisplay.map((post, index) => {
                            return renderPost(post, index)
                        })
                        : <div className={'w-full text-center'}>
                            {translationObject.no_results}
                        </div>}
                </div>
            </div>
            <div className={'container flex justify-center items-center my-24 flex-col gap-y-6'}>
                {isCurrentlyLoading
                    ? <span className={'loading-spinner'}/>
                    : morePostsToDisplay() && (
                    <button onClick={showMore} disabled={!morePostsToDisplay()}
                            className="inline-flex items-center gap-x-2.5">
                        <PlusButtonIcon/>
                        {translationObject.load_more}
                    </button>
                )
                }
                <p className={'text-base leading-normal italic'}>
                {postsToDisplay.length} von {allPosts.length} Produkten
                </p>
            </div>
        </div>
    )
}

export default FilterNew;
