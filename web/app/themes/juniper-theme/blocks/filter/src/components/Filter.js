import React, { useState, useEffect } from "react"
import AlternatingResult from "./AlternatingResult"
import ArticleResult from "./ArticleResult"
import axios from 'axios'
import { useMediaQuery } from 'react-responsive'
import Checkbox from "./Checkbox"

const Filter = ( data ) => {
    const [selectedFilterVals, setSelectedFilterVals] = useState({
        search: '',
        taxonomies: []
    })
    const [posts, setPosts] = useState(data.posts)
    const [page, setPage] = useState(1)
    const [loadingMore, setLoadingMore] = useState(false)
    const [maxPages, setMaxPages] = useState(data.maxNumPages)
    const [showFilterItems, setShowFilterItems] = useState(false)
    const isMobile = useMediaQuery({ query: `(max-width: 640px)` })
    const [firstPageLoad, setFirstPageLoad] = useState(true)
    

    const updateFilterVals = (e, term_id) => {
        e.preventDefault()
        let shallowFilterVals = [...selectedFilterVals]
        if(shallowFilterVals.includes(term_id)) {
            shallowFilterVals = shallowFilterVals.splice(shallowFilterVals.indexOf(term_id), 1)
        } else {
            shallowFilterVals.push(term_id)
        }

        setSelectedFilterVals(shallowFilterVals)
    }

    const loadMorePosts = () => {
        setLoadingMore(true)
        setPage(page + 1)
    }

    const removeTerm = ( event, termId ) => {
        event.stopPropagation()
        let newSelectedFilterVals = [...selectedFilterVals],
            targetIndex = newSelectedFilterVals.indexOf(termId)
        newSelectedFilterVals.splice(targetIndex, 1)
        setSelectedFilterVals(newSelectedFilterVals)
    }

    const searchPosts = () => {
        let queryString = `?post_type=${data.postType}`
        queryString += `&search=${encodeURIComponent(selectedFilterVals.search)}`
        let taxonomies = JSON.stringify(selectedFilterVals.taxonomies)
        queryString += `&taxonomies=${encodeURIComponent(taxonomies)}`

        queryString += `&page=${page}`
        axios.get(`${data.restUrl}wps/v1/data${queryString}`)
            .then(res => {
                if(page > 1) {
                    setPosts([...posts, ...res.data.posts])
                } else {
                    setPosts(res.data.posts)
                }
                setMaxPages(res.data.maxNumPages)
                setLoadingMore(false)
            })
            .catch(err => {
                console.error(err)
            })
    }
    
    const toggleFilterOpen = (e) => {
        e.preventDefault()
        setShowFilterItems(!showFilterItems)
    }

    const searchByText = (e) => {
        e.preventDefault()
        setSelectedFilterVals({
            ...selectedFilterVals,
            search: e.currentTarget.value
        })
    }

    const handleTaxSelect = (name, e) => {
        e.preventDefault()
        setSelectedFilterVals({
            ...selectedFilterVals,
            taxonomies: [
                ...selectedFilterVals.taxonomies,
                {
                    name: name,
                    value: [e.target.value]
                }
            ]
        })
    }


    useEffect(() => {
        if(firstPageLoad) {
            setFirstPageLoad(false)
        }

        if(selectedFilterVals && !firstPageLoad) {
            const delayDebounceFn = setTimeout(() => {
                // add in later
                //window.history.replaceState(null, null, `?search=${encodeURIComponent(searchTerm)}&type=${encodeURIComponent(exerciseType)}`)
                searchPosts()
            }, 400)
          
            return () => clearTimeout(delayDebounceFn)
        }
        return
    }, [selectedFilterVals])

    useEffect(() => {
        // window.addEventListener("resize", handleResize)
        if(!isMobile) setShowFilterItems(true)
    }, [isMobile])
  

    return (
        <div className="w-full">
            <div className="container">
                <div className="flex items-center border-b py-2 max-w-[50%]">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16" fill="none">
                        <path d="M0.852114 14.3519L4.37266 10.8321C3.35227 9.60705 2.84344 8.03577 2.95204 6.44512C3.06064 4.85447 3.7783 3.36692 4.95573 2.29193C6.13316 1.21693 7.67971 0.637251 9.27365 0.673476C10.8676 0.709701 12.3862 1.35904 13.5136 2.48642C14.641 3.6138 15.2903 5.13241 15.3265 6.72635C15.3627 8.32029 14.7831 9.86684 13.7081 11.0443C12.6331 12.2217 11.1455 12.9394 9.55488 13.048C7.96423 13.1566 6.39295 12.6477 5.1679 11.6273L1.64805 15.1479C1.59579 15.2001 1.53375 15.2416 1.46546 15.2699C1.39718 15.2982 1.32399 15.3127 1.25008 15.3127C1.17617 15.3127 1.10299 15.2982 1.0347 15.2699C0.96642 15.2416 0.904376 15.2001 0.852114 15.1479C0.799852 15.0956 0.758396 15.0336 0.730112 14.9653C0.701828 14.897 0.68727 14.8238 0.68727 14.7499C0.68727 14.676 0.701828 14.6028 0.730112 14.5345C0.758396 14.4663 0.799852 14.4042 0.852114 14.3519ZM14.1876 6.87492C14.1876 5.87365 13.8907 4.89487 13.3344 4.06234C12.7781 3.22982 11.9875 2.58094 11.0624 2.19778C10.1374 1.81461 9.11947 1.71435 8.13744 1.90969C7.15541 2.10503 6.25336 2.58718 5.54536 3.29519C4.83735 4.00319 4.3552 4.90524 4.15986 5.88727C3.96452 6.8693 4.06477 7.8872 4.44794 8.81225C4.83111 9.7373 5.47999 10.528 6.31251 11.0842C7.14503 11.6405 8.12382 11.9374 9.12508 11.9374C10.4673 11.9359 11.7541 11.4021 12.7032 10.453C13.6522 9.50392 14.1861 8.21712 14.1876 6.87492Z" fill="black"/>
                    </svg>
                    <input 
                        className="appearance-none bg-transparent border-none w-full text-gray-700 mr-3 py-1 px-2 leading-tight focus:outline-none" 
                        type="text" 
                        placeholder="Search products..." 
                        aria-label="product search"
                        onChange={(e) => searchByText(e)} 
                    />
                </div>
            </div>
            <div className="container mx-auto">
                {isMobile ? 
                    <div className="w-full flex justify-center items-center">
                        <h3 className="text-white">Filter</h3>
                        <button 
                            type="button" 
                            className={`filter-toggle inline-flex items-center p-2 justify-center text-sm ml-4 ${showFilterItems ? 'open' : 'closed'}`}
                            onClick={toggleFilterOpen}
                        >
                            <span className="sr-only">Open filter</span>
                            <svg className="w-[2.5rem] h-[2.5rem] open-toggle" xmlns="http://www.w3.org/2000/svg" width="40" height="41" viewBox="0 0 40 41" fill="none">
                                <path d="M6.66669 35.5929V23.9263" stroke="#F9F9F9" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"/>
                                <path d="M6.66669 17.2594V5.59277" stroke="#F9F9F9" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"/>
                                <path d="M20 35.5928V20.5928" stroke="#F9F9F9" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"/>
                                <path d="M20 13.9261V5.59277" stroke="#F9F9F9" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"/>
                                <path d="M33.3333 35.5926V27.2593" stroke="#F9F9F9" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"/>
                                <path d="M33.3333 20.5928V5.59277" stroke="#F9F9F9" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"/>
                                <path d="M1.66669 23.9263H11.6667" stroke="#F9F9F9" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"/>
                                <path d="M15 13.9263H25" stroke="#F9F9F9" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"/>
                                <path d="M28.3333 27.2593H38.3333" stroke="#F9F9F9" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"/>
                            </svg>
                            <svg className="w-[2.5rem] h-[2.5rem] close-toggle" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 35 35" fill="none">
                                <path d="M26.25 8.75L8.75 26.25" stroke="#F9F9F9" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"/>
                                <path d="M8.75 8.75L26.25 26.25" stroke="#F9F9F9" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"/>
                            </svg>
                        </button>
                    </div>
                : null}
                {showFilterItems ? 
                    <div id="filter-items" className="grid grid-cols-12 justify-start py-20">
                        {data.filterOptions.map((filterItem, key) => {
                            if(filterItem.type === "dropdown") {
                                return (
                                    <div key={key} className="col-span-12 relative max-w-64">
                                        <label>{filterItem.label}</label>
                                        <select 
                                            onChange={(e) => handleTaxSelect(filterItem.name, e)} 
                                            className="block appearance-none w-full bg-white border border-gray-400 hover:border-gray-500 px-4 py-2 pr-8 rounded shadow leading-tight focus:outline-none focus:shadow-outline"
                                        >
                                            <option value="none">None</option>
                                            {filterItem.tax_options.map((term, index) => {
                                                return <option key={index} value={term.term_id}>{term.name}</option>
                                            })}
                                        </select>
                                        <div className="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700">
                                            <svg className="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path d="M9.293 12.95l.707.707L15.657 8l-1.414-1.414L10 10.828 5.757 6.586 4.343 8z"/></svg>
                                        </div>
                                    </div>
                                )
                            }
                            if(filterItem.type === "checkbox") {
                                return (
                                    <div key={key} className="col-span-12 block">
                                        <label>{filterItem.label}</label>
                                        {filterItem.tax_options.map((term, index) => {
                                            return (
                                                <Checkbox key={index} term={term} filterItem={filterItem} handleTaxSelect={handleTaxSelect} />
                                            )
                                        })}
                                    </div>
                                )
                            }
                            return null
                        })}
                        
                        {/* {data.terms.map((term, index) => {
                            if(term.slug === "uncategorized") return null
                            
                            let isActive = false
                            //selectedFilterVals.includes(term.term_id)
                            return (
                                <button key={index} className={`filter-btn w-fit inline-flex items-center ${isActive ? 'active' : ''}`} type="button" onClick={(e) => updateFilterVals(e, term.term_id)}>
                                    <span className={`${isActive ? 'bg-accent' : 'bg-light'} self-stretch p-[0.375rem]`}><img className="object-contain" src={term.fields.svg_icon} alt="Term Icon" /></span>
                                    <span className="btn-inner">{term.name}</span>
                                    {isActive ? 
                                        <span className="remove-term" onClick={(event) => removeTerm(event, term.term_id)}>
                                            <svg xmlns="http://www.w3.org/2000/svg" width="11" height="11" viewBox="0 0 11 11" fill="none">
                                                <path d="M7.89258 3.23987L2.89258 8.23987" stroke="#093642" strokeWidth="1.5" strokeLinecap="round" strokeLinejoin="round"/>
                                                <path d="M2.89258 3.23987L7.89258 8.23987" stroke="#093642" strokeWidth="1.5" strokeLinecap="round" strokeLinejoin="round"/>
                                            </svg>
                                        </span>
                                    : null}
                                </button>
                            )
                        })} */}
                    </div>
                : null}
            </div>
            <div className="container">
                <div className="grid grid-cols-3 mb-10">
                    {posts.length ? 
                        <>
                            {posts.map((post, index) => {
                                return (
                                    <div key={index} className="max-w-sm rounded overflow-hidden shadow-lg">
                                        <div className="font-bold text-xl mb-2">{post.post_title}</div>
                                    </div>
                                )
                            })}
                        </>
                    : 
                        <div className="w-full text-center">
                            keine Ergebnisse.
                        </div>
                    }
                </div>
            </div>
            {loadingMore ? 
                <div className="container flex justify-center">
                    <p>Loading...</p>
                </div>
            : 
                <div className="container flex justify-center">
                    {page < maxPages ? 
                        <button onClick={() => loadMorePosts()} className="btn btn-primary w-full">Mehr {data.postName} zeigen</button>
                    : null}
                </div>
            }
        </div>
    )
}

export default Filter