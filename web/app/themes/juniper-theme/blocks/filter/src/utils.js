import React from "react";
import FilterDropdown from "./newComponents/SingleFilterComponents/Dropdown/FilterDropdown";
import axios from "axios";
import SingleProduct from "./newComponents/ProductComponent/SingleProduct";

export function isIterable(obj) {
    // checks for null and undefined
    return typeof obj[Symbol.iterator] === 'function';
}

export function isArray(variable) {
    return Array.isArray(variable)
}

export function rerenderSlick() {
    const event = new Event('filterRenderingDone');
    document.dispatchEvent(event);
}

export function refreshSlick() {
    const event = new Event('filterRefreshRenderedElements')
    document.dispatchEvent(event)
}

export function clone(obj) {
    return JSON.parse(JSON.stringify(obj))
}

export async function loadInPostsFromPage(endpointUrl = '', postType = 'product', pageNum = 0, nocache) {
    const endpoint = `${endpointUrl}&post_type=${postType}&page=${pageNum}&nocache=${nocache}`
    const response = await axios.get(endpoint)

    const responseData = response.data ?? {posts: []}

    return responseData.data;
}

export function renderPost(post, index, showDirectly = false, whenInView = (() => {})) {
    return <SingleProduct
        index={index}
        htmlEnc={post.html}
        showDirectly={showDirectly}
        whenInView={whenInView}
    />
}

export function renderMock(mockHtml) {
    return <SingleProduct
        index={Math.random()}
        htmlEnc={mockHtml}
        showDirectly={true}
        whenInView={() => {}}
    />
}
export function loadingElement() {
    return <div className={'absolute z-10 w-full py-12'}>
        <div className='flex space-x-2 justify-center items-center bg-transparent'>
            <span className='sr-only'>Loading...</span>
            <div className='h-8 w-8 bg-black rounded-full animate-bounce [animation-delay:-0.3s]'></div>
            <div
                className='h-8 w-8 bg-black rounded-full animate-bounce [animation-delay:-0.15s]'></div>
            <div className='h-8 w-8 bg-black rounded-full animate-bounce'></div>
        </div>
    </div>;
}
export function getUrlParamValue(param) {
    const currentQuery = window.location.search.substring(1)
    
    const vars = currentQuery.split('&')

    const queryArray = vars.map((keyValue) => {
        const key = keyValue.split('=')[0]
        const value = keyValue.split('=')[1]
        return {key: key, value: value}
    })
    
    const foundValue = queryArray.find((element) => element.key === param)

    return foundValue
        ? decodeURIComponent(foundValue.value)
        : ''
}

export function filterOptionToElement(filterOption) {
    return <FilterDropdown data={filterOption}/>
}

export function postApplysToTax(post, tax, value) {
    const taxonomies = post.taxonomies

    const taxonomyToChecExists = taxonomies[tax] !== undefined

    if(!taxonomyToChecExists) {
        return false
    }

    const taxonomyToCheck = taxonomies[tax]

    return taxonomyToCheck.findIndex((taxObj) => {
        return taxObj.term_id === value
    }) !== -1
}

export function postInSelection(taxonomyName, taxonomyValue, post) {
    // noinspection JSCheckFunctionSignatures
    taxonomyValue = parseInt(taxonomyValue)

    return postApplysToTax(post, taxonomyName, taxonomyValue)
}

export function postInTextSelection(text, post) {
    return post.post_title.toLowerCase().includes(text)
        || post.excerpt.toLowerCase().includes(text)
        || post.description_text && post.description_text.toLowerCase().includes(text)
        || post.description_title && post.description_title.toLowerCase().includes(text)
        || post.subheadline && post.subheadline.toLowerCase().includes(text)
        || post.features_text && post.features_text.toLowerCase().includes(text)
        || post.areas_of_application && post.areas_of_application.toLowerCase().includes(text)
        || Object.values(post.taxonomies).some(taxonomy => taxonomy.some(term => term.name.toLowerCase().includes(text)))
}

export function postIsAvailableOnline(post) {
    return post.price != null && post.price > 0
}

export function postHasSampleAvailable(post) {
    return post.taxonomies["purchasability"]?.some(term => term.slug === 'muster-verfuegbar' || term.slug === 'sample-available-en')
}

export function hideOptionName(option, parent) {
    return parent !== undefined && (parent.slug === 'weisstoene' || parent.slug === 'white-tone')
}