import React, {useEffect, useReducer, useState} from "react";
import axios from 'axios';
import {useMediaQuery} from "react-responsive";
import MobileFilter from "./SingleFilterComponents/MobileFilter";
import {
    filterOptionToElement,
    loadInPostsFromPage,
    renderPost,
    rerenderSlick
} from "../utils";
import {PlusButtonIcon} from "./Icons";
import FilterTextSearch from "./SingleFilterComponents/FilterTextSearch";

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

    const [filterOptions, setFilterOptions] = useState([])

    const [filterSelected, setFilterSelected] = useState({})

    const [shouldShowFilterItems, showFilterItems] = useState(true)

    const [allPosts, setAllPosts] = useState(data.posts)

    const [filteredPosts, setFilteredPosts] = useState(data.posts)
    const [postsToDisplay, setPostsToDisplay] = useState(data.posts.slice(0, postsPerPage))

    const [isCurrentlyLoading, currentlyLoading] = useState(false)

    const currentlyMobile = useMediaQuery({query: '(max-width: 640px) '})

    function morePostsToDisplay() {
        return postsToDisplay.length < filteredPosts.length
    }

    function showMore() {
        const current = postsToDisplay.length ?? 0

        const nextPosts = filteredPosts.slice(current, postsPerPage + current)

        setPostsToDisplay(postsToDisplay.concat(nextPosts))
    }

    function loadPosts(lastAdded = 1, currentPage = 0, postsPulled = []) {
        if (lastAdded <= 0) {
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

    /**
     * @param filter {Object}
     */
    function applyFilter(filter) {
        const filterOptions = Object.entries(filter)

        // TODO: implement filtering of taxonomies
        // one filter key exquals to one taxonomy to search for
        // the filter value is the value to filter
        console.log(allPosts)

        let toFilterData = allPosts

        filterOptions.forEach((tupel) => {
            const taxonomy = tupel[0]
            const value = tupel[1]

            //TODO: ain't filering right, probably .find issue
            toFilterData = toFilterData.filter((element) => {
                const objTaxonomy = element.taxonomies[taxonomy]

                if(objTaxonomy === undefined) {
                    return false
                }

                return objTaxonomy.find((taxEntry) => {
                    console.log(taxEntry.term_id)
                    console.log(value)

                    return taxEntry.term_id === value
                })
            })
        })

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
            return filterOption
        })

        // move checkboxes to the back
        preparedOptions.sort((filterOption) => {return filterOption.type === 'checkbox' ? 1 : -1})

        setFilterOptions(preparedOptions)
    }

    useEffect(() => {
        setFilteredPosts(allPosts)
    }, [allPosts]);

    useEffect(() => {
        applyFilter(filterSelected)
    }, [filterSelected]);

    useEffect(() => {
        const currentAmontShown = postsToDisplay.length
        setPostsToDisplay(filteredPosts.slice(0, currentAmontShown))
    }, [filteredPosts]);


    useEffect(() => {
        loadPosts()
        setUpFilters()
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
                        <PlusButtonIcon />
                        {translationObject.load_more}
                    </button>
                )
                }
                <p className={'text-base leading-normal italic'}>
                    {postsToDisplay.length} von {filteredPosts.length} Produkten
                </p>
            </div>
        </div>
    )
}

export default FilterNew;
