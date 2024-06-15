import React, {useEffect, useState} from "react";
import {
    filterOptionToElement, isArray, loadingElement, loadInPostsFromPage, postHasSampleAvailable,
    postInSelection,
    postInTextSelection,
    postIsAvailableOnline, refreshSlick, renderMock,
    renderPost,
    rerenderSlick
} from "../utils";
import FilterTextSearch from "./SingleFilterComponents/Text/FilterTextSearch";
import FilterCheckbox from "./SingleFilterComponents/Checkbox/FilterCheckbox";
import translationObject from "../TranslationObject";

const Filter = (data) => {
    const title = data.title ?? '';

    const postType = data.postType ?? 'product'

    const endpoint = data.pullEndpoint

    const nocache = data.nocache ?? false

    const sample_available = data.sample_available
    const online_available = data.online_available

    const show_sample_available_filter = sample_available === 'filter'
    const show_online_available_filter = online_available === 'filter'

    const always_filter_sample_available = sample_available === 'outright'
    const always_filter_online_available = online_available === 'outright'

    const postsPerPage = 6

    // mocked card render
    const mockedCard = data.mocked

    // filter options that get displayed
    const [filterOptions, setFilterOptions] = useState([])
    // selection of the filter (what to filter for)
    const [filterSelected, setFilterSelected] = useState({})

    // all posts that exist
    const [allPosts, setAllPosts] = useState([])
    // posts after being run through the filter
    const [filteredPosts, setFilteredPosts] = useState([])

    const [loading, isLoading] = useState(true);

    function loadPosts() {
        isLoading(true)
        const posts = loadInPostsFromPage(endpoint, postType, 0, nocache);

        posts.then((data) => {
            setAllPosts(data)
            isLoading(false)
        })
    }

    function applyFilter(filter) {
        let filterOptions = Object.entries(filter).filter(keyValue => keyValue[1] !== '')

        let toFilterData = allPosts

        // filter out false and empty values
        filterOptions = filterOptions.filter((filter) => filter[1] && filter[1].length !== 0)

        for (const filterOption of filterOptions) {
            const filterOptionName = filterOption[0]
            const filterValue = filterOption[1]

            switch (filterOptionName) {
                case 'searchText':
                    toFilterData = toFilterData.filter((post) => postInTextSelection(filterValue.toLowerCase().trim(), post))
                    break
                case 'sampleAvailable':
                    toFilterData = toFilterData.filter(postHasSampleAvailable)
                    break
                case 'onlineAvailable':
                    toFilterData = toFilterData.filter(postIsAvailableOnline)
                    break
                default:
                    if (isArray(filterValue)) {
                        toFilterData = toFilterData.filter((post) => filterValue.some((singleValue) => postInSelection(filterOptionName, singleValue.value, post)))
                    } else {
                        toFilterData = toFilterData.filter((post) => postInSelection(filterOptionName, filterValue.value, post))
                    }
            }
        }

        setFilteredPosts(toFilterData)
    }

    function setUpFilterPresets() {
        setFilterSelected(prevFilter => ({
            ...prevFilter,
            sampleAvailable: always_filter_sample_available,
            onlineAvailable: always_filter_online_available
        }))
    }

    function applyValueToFilter(filterKey, filterValue) {
        setFilterSelected((prevFilter) => {
            return {
                ...prevFilter,
                [filterKey]: filterValue
            }
        })
    }

    function setUpFilters() {
        const filterOptions = data.filterOptions ?? []

        const preparedOptions = filterOptions.map((filterOption) => {
            filterOption.onChange = (selected) => {
                applyValueToFilter(filterOption.filter_choices, selected)
            }

            filterOption.label = translationObject[filterOption.name]
                ? translationObject[filterOption.name]
                : filterOption.label

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

    useEffect(async() => {
        setUpFilters()
        setUpFilterPresets()
        loadPosts()
        rerenderSlick()
    }, []);

    useEffect(rerenderSlick, [filteredPosts]);

    return (
        <div className={"w-full container"}>
            <div className={""}>
                <h1
                    data-aos={'fade-up'}
                    className={"mb-0 sm:mb-6"}>{title}</h1>
            </div>
            <div className={"mx-auto"}>
                <div id={'filter-items'}
                     data-aos={'fade-up'}
                     data-aos-delay={'50'}
                     data-aos-offset={0}
                     className={'grid grid-cols-12 justify-start mt-6 sm:mt-0'}>
                    <FilterTextSearch
                        label={'Product Search'}
                        name={'Product Search'}
                        url={'text'}
                        placeholder={translationObject.product_search}
                        onChange={(newValue) =>
                            applyValueToFilter('searchText', newValue.trim().toLowerCase())
                        }
                    />
                </div>

                <div data-aos={'fade-up'}
                     data-aos-delay={'100'}
                     data-aos-offset={0}
                     className={'grid grid-cols-1 md:grid-cols-3 relative z-10 sm:gap-7 mt-6 sm:mt-0'}>
                    {filterOptions.map(filterOptionToElement)}
                </div>

                <div data-aos={'fade-up'}
                     data-aos-delay={'150'}
                     data-aos-offset={0}
                     className="flex flex-row justify-between gap-5 col-span-12">
                    <div className={'flex flex-row gap-5'}>
                        {show_sample_available_filter && <FilterCheckbox
                            key={'sampleAvailable'}
                            name={'sampleAvailable'}
                            label={translation.filter_sample_available}
                            url={'purchasability'}
                            onChange={(isChecked) => setFilterSelected(prevFilters => (
                                {
                                    ...prevFilters,
                                    sampleAvailable: isChecked
                                }
                            ))}
                        />}
                        {show_online_available_filter && <FilterCheckbox
                            key={'onlineAvailable'}
                            name={'onlineAvailable'}
                            label={translation.filter_online_available}
                            url={'online-available'}
                            isChecked={filterSelected.onlineAvailable}
                            onChange={(isChecked) => setFilterSelected(prevFilters => (
                                {
                                    ...prevFilters,
                                    onlineAvailable: isChecked
                                }
                            ))}
                        />}
                    </div>
                    <div className={'flex flex-row gap-3 text-sm font-medium text-gray-900 mr-1'}>
                        {translationObject.results_label}: {filteredPosts?.length}
                    </div>
                </div>
            </div>
            <div className={'mt-5 relative isolate'}>
                {loading && loadingElement()}
                <div className={"grid grid-cols-1 md:grid-cols-3 md:mb-10 md:gap-7 filter-grid flex-wrap"}>
                    {allPosts?.length === 0 && [...Array(6).keys()].map(() => renderMock(mockedCard))}
                    {filteredPosts?.length ?
                        filteredPosts.map((post, index) => {
                            const showDirectly = index < postsPerPage
                            return renderPost(post, index, false, refreshSlick)
                        })
                        : <div className={'w-full text-center col-span-3'}>
                            {translationObject.no_results}
                        </div>}
                </div>
            </div>
        </div>
    )
}

export default Filter;
