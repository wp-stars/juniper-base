import React, { useState, useEffect } from "react"
import axios from 'axios'
import { useMediaQuery } from 'react-responsive'
import Checkbox from "./Checkbox"

const Filter = (data) => {
    console.log(data)
    const [originalDisplayedPosts, setOriginalDisplayedPosts] = useState(
        data.posts.filter(post => post.product_type !== "musterbestellung").slice(0, 6)
    );
    const [filteredPosts, setFilteredPosts] = useState([]);

    const [displayedPosts, setDisplayedPosts] = useState(originalDisplayedPosts);
    const [filters, setFilters] = useState({
        searchText: "",
        purchasability: false,
        metalsAndAccessories: undefined,
        color: undefined,
        productCat: undefined,
        onlineAvailable: false // New filter state

    });

    const [page, setPage] = useState(1)
    const [loading, setLoading] = useState(false)
    const [maxPages, setMaxPages] = useState(data.maxNumPages)
    const [showFilterItems, setShowFilterItems] = useState(false)
    const isMobile = useMediaQuery({ query: `(max-width: 640px)` })




    const searchPosts = async (pageNum) => {
        if (pageNum > maxPages) return;
        try {
            const response = await axios.get(`${data.restUrl}wps/v1/data?post_type=${data.postType}&page=${pageNum}`);
            console.log(response.data, "response")
            if (response.data && response.data.posts.length > 0) {
                setOriginalDisplayedPosts(prevPosts => [...prevPosts, ...response.data.posts]);
                if (pageNum === 1) {
                    setDisplayedPosts(response.data.posts);
                }
            }
            setMaxPages(response.data.maxPages || maxPages);
        } catch (error) {
            console.error(error);
        }
    };

    const toggleFilterOpen = (e) => {
        e.preventDefault()
        setShowFilterItems(!showFilterItems);
        if (!showFilterItems) {
        }
    }

    const loadMorePosts = () => {
        const nextPostsToShow = filteredPosts.slice(displayedPosts.length, displayedPosts.length + 6);
        setDisplayedPosts(displayedPosts.concat(nextPostsToShow));
        eventSlider();
    };


    const calculateDisplayedRange = () => {
        const totalPosts = filteredPosts.length;
        const lastPostIndex = displayedPosts.length;
        return `${lastPostIndex} of ${totalPosts} products`;
    };

    const handleTaxSelect = (name, e) => {
        if (name === "purchasability" || name === "metals-and-accessories") {
            setFilters(prevFilters => ({ ...prevFilters, [name]: !prevFilters[name] }));
        } else {
            const selectedValue = e.target.value;
            setFilters(prevFilters => ({ ...prevFilters, [name]: selectedValue }));
        }
    };


    const applyFilters = ({ searchText, purchasability, metalsAndAccessories, color, productCat, onlineAvailable }) => {
        let filtered = originalDisplayedPosts;

        if (searchText) {
            filtered = filtered.filter(post =>
                post.post_title.toLowerCase().includes(searchText) 
                ||
                post.excerpt.toLowerCase().includes(searchText) ||
                (post.description_text && post.description_text.toLowerCase().includes(searchText)) ||  
                (post.description_title && post.description_title.toLowerCase().includes(searchText)) || 
                (post.subheadline && post.subheadline.toLowerCase().includes(searchText)) ||
                (post.features_text && post.features_text.toLowerCase().includes(searchText)) ||
                (post.areas_of_application && post.areas_of_application.toLowerCase().includes(searchText)) ||
                Object.values(post.taxonomies).some(taxonomy => 
                    taxonomy.some(term => term.name.toLowerCase().includes(searchText))
                )
            );
        }
        

        if (purchasability) {
            filtered = filtered.filter(post => post.taxonomies["purchasability"]?.some(term => term.slug === "sample-available"));
        }

        if (metalsAndAccessories && metalsAndAccessories !== "none") {
            filtered = filtered.filter(post => post.taxonomies["metals-and-accessories"]?.some(term => term.term_id === parseInt(metalsAndAccessories)));
        }

        if (color && color !== "none") {
            filtered = filtered.filter(post => post.taxonomies["color"]?.some(term => term.term_id === parseInt(color)));
        }

        if (productCat && productCat !== "none") {
            filtered = filtered.filter(post => post.taxonomies["product_cat"]?.some(term => term.term_id === parseInt(productCat)));
        }

        if (onlineAvailable) {
            filtered = filtered.filter(post => post.price != null && post.price > 0);
        }

        setFilteredPosts(filtered);
        setDisplayedPosts(filtered.slice(0, 6));
    };



    const resetFilters = () => {
        setFilters({
            searchText: "",
            purchasability: false,
            metalsAndAccessories: 'none',
            color: 'none',
            productCat: 'none',
            onlineAvailable: false
        });
    }

    const eventSlider = () => {
        const event = new Event('filterRenderingDone');
        document.dispatchEvent(event);
    };



    useEffect(() => {
        applyFilters(filters);
    }, [filters, originalDisplayedPosts]);



    useEffect(() => {
        const startIndex = (page - 1) * 6;
        const endIndex = startIndex + 6;
        setDisplayedPosts(originalDisplayedPosts.slice(startIndex, endIndex));
    }, [page, originalDisplayedPosts]);


    useEffect(() => {
        if (data.posts.length && maxPages > 1) {
            for (let i = 2; i <= maxPages; i++) {
                searchPosts(i);
            }
        }
        console.log(displayedPosts)
    }, [maxPages]);


    useEffect(() => {
        if (!isMobile) setShowFilterItems(true)
    }, [isMobile])

    useEffect(() => {
        eventSlider();
    }, [originalDisplayedPosts, displayedPosts]);


    return (
        <div className="w-full">
            <div className="container">
                <h1 className=" mb-0 sm:mb-[30px]">{data.title}</h1>
            </div>
            <div className="container mx-auto">
                {isMobile ?
                    <div className="w-full flex justify-end items-center mt-3 sm:mt-[-55px]">
                        <button
                            type="button"
                            className={`filter-toggle inline-flex items-center p-2 justify-center text-sm ml-4 ${showFilterItems ? 'open' : 'closed'}`}
                            onClick={toggleFilterOpen}
                        >
                            <span className="sr-only">{translation.open_filter}</span>
                            <svg className="w-[2.5rem] h-[2.5rem] open-toggle" xmlns="http://www.w3.org/2000/svg" width="40" height="41" viewBox="0 0 40 41" fill="none">
                                <path xmlns="http://www.w3.org/2000/svg" id="Vector" d="M6.25 13.4375H11.6562C11.8714 14.4971 12.4463 15.4498 13.2835 16.1341C14.1207 16.8184 15.1687 17.1922 16.25 17.1922C17.3313 17.1922 18.3793 16.8184 19.2165 16.1341C20.0537 15.4498 20.6286 14.4971 20.8437 13.4375H33.75C33.9986 13.4375 34.2371 13.3387 34.4129 13.1629C34.5887 12.9871 34.6875 12.7486 34.6875 12.5C34.6875 12.2514 34.5887 12.0129 34.4129 11.8371C34.2371 11.6613 33.9986 11.5625 33.75 11.5625H20.8437C20.6286 10.5029 20.0537 9.55018 19.2165 8.8659C18.3793 8.18161 17.3313 7.8078 16.25 7.8078C15.1687 7.8078 14.1207 8.18161 13.2835 8.8659C12.4463 9.55018 11.8714 10.5029 11.6562 11.5625H6.25C6.00136 11.5625 5.7629 11.6613 5.58709 11.8371C5.41127 12.0129 5.3125 12.2514 5.3125 12.5C5.3125 12.7486 5.41127 12.9871 5.58709 13.1629C5.7629 13.3387 6.00136 13.4375 6.25 13.4375ZM16.25 9.6875C16.8063 9.6875 17.35 9.85245 17.8125 10.1615C18.2751 10.4705 18.6355 10.9098 18.8484 11.4237C19.0613 11.9376 19.117 12.5031 19.0085 13.0487C18.8999 13.5943 18.6321 14.0954 18.2387 14.4887C17.8454 14.8821 17.3443 15.1499 16.7987 15.2585C16.2531 15.367 15.6876 15.3113 15.1737 15.0984C14.6598 14.8855 14.2205 14.5251 13.9115 14.0625C13.6025 13.6 13.4375 13.0563 13.4375 12.5C13.4375 11.7541 13.7338 11.0387 14.2613 10.5113C14.7887 9.98382 15.5041 9.6875 16.25 9.6875ZM33.75 26.5625H30.8438C30.6286 25.5029 30.0537 24.5502 29.2165 23.8659C28.3793 23.1816 27.3313 22.8078 26.25 22.8078C25.1687 22.8078 24.1207 23.1816 23.2835 23.8659C22.4463 24.5502 21.8714 25.5029 21.6562 26.5625H6.25C6.00136 26.5625 5.7629 26.6613 5.58709 26.8371C5.41127 27.0129 5.3125 27.2514 5.3125 27.5C5.3125 27.7486 5.41127 27.9871 5.58709 28.1629C5.7629 28.3387 6.00136 28.4375 6.25 28.4375H21.6562C21.8714 29.4971 22.4463 30.4498 23.2835 31.1341C24.1207 31.8184 25.1687 32.1922 26.25 32.1922C27.3313 32.1922 28.3793 31.8184 29.2165 31.1341C30.0537 30.4498 30.6286 29.4971 30.8438 28.4375H33.75C33.9986 28.4375 34.2371 28.3387 34.4129 28.1629C34.5887 27.9871 34.6875 27.7486 34.6875 27.5C34.6875 27.2514 34.5887 27.0129 34.4129 26.8371C34.2371 26.6613 33.9986 26.5625 33.75 26.5625ZM26.25 30.3125C25.6937 30.3125 25.15 30.1475 24.6875 29.8385C24.2249 29.5295 23.8645 29.0902 23.6516 28.5763C23.4387 28.0624 23.383 27.4969 23.4915 26.9513C23.6001 26.4057 23.8679 25.9046 24.2613 25.5113C24.6546 25.1179 25.1557 24.8501 25.7013 24.7415C26.2469 24.633 26.8124 24.6887 27.3263 24.9016C27.8402 25.1145 28.2795 25.4749 28.5885 25.9375C28.8975 26.4 29.0625 26.9437 29.0625 27.5C29.0625 28.2459 28.7662 28.9613 28.2387 29.4887C27.7113 30.0162 26.9959 30.3125 26.25 30.3125Z" fill="black" />
                            </svg>
                            <svg className="w-[2.5rem] h-[2.5rem] close-toggle" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 35 35" fill="none">
                                <path d="M26.25 8.75L8.75 26.25" stroke="#000" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" />
                                <path d="M8.75 8.75L26.25 26.25" stroke="#000" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" />
                            </svg>
                        </button>
                    </div>
                    : null}
                {showFilterItems ?  
                
                    <div id="filter-items" className="grid grid-cols-12 justify-start mt-[30px] sm:mt-0">
                        <div className="flex items-center border-b py-2 col-span-12 max-w-96 mb-7 focus-visible:border-0">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16" fill="none">
                                <path d="M0.852114 14.3519L4.37266 10.8321C3.35227 9.60705 2.84344 8.03577 2.95204 6.44512C3.06064 4.85447 3.7783 3.36692 4.95573 2.29193C6.13316 1.21693 7.67971 0.637251 9.27365 0.673476C10.8676 0.709701 12.3862 1.35904 13.5136 2.48642C14.641 3.6138 15.2903 5.13241 15.3265 6.72635C15.3627 8.32029 14.7831 9.86684 13.7081 11.0443C12.6331 12.2217 11.1455 12.9394 9.55488 13.048C7.96423 13.1566 6.39295 12.6477 5.1679 11.6273L1.64805 15.1479C1.59579 15.2001 1.53375 15.2416 1.46546 15.2699C1.39718 15.2982 1.32399 15.3127 1.25008 15.3127C1.17617 15.3127 1.10299 15.2982 1.0347 15.2699C0.96642 15.2416 0.904376 15.2001 0.852114 15.1479C0.799852 15.0956 0.758396 15.0336 0.730112 14.9653C0.701828 14.897 0.68727 14.8238 0.68727 14.7499C0.68727 14.676 0.701828 14.6028 0.730112 14.5345C0.758396 14.4663 0.799852 14.4042 0.852114 14.3519ZM14.1876 6.87492C14.1876 5.87365 13.8907 4.89487 13.3344 4.06234C12.7781 3.22982 11.9875 2.58094 11.0624 2.19778C10.1374 1.81461 9.11947 1.71435 8.13744 1.90969C7.15541 2.10503 6.25336 2.58718 5.54536 3.29519C4.83735 4.00319 4.3552 4.90524 4.15986 5.88727C3.96452 6.8693 4.06477 7.8872 4.44794 8.81225C4.83111 9.7373 5.47999 10.528 6.31251 11.0842C7.14503 11.6405 8.12382 11.9374 9.12508 11.9374C10.4673 11.9359 11.7541 11.4021 12.7032 10.453C13.6522 9.50392 14.1861 8.21712 14.1876 6.87492Z" fill="black" />
                            </svg>
                            <input
                                className="appearance-none bg-transparent border-none w-full text-[#737373] mr-3 py-1 px-2 leading-tight focus:ring-transparent focus:shadow-none focus-visible:ring-transparent"
                                type="text"
                                placeholder={translation.product_search}
                                aria-label="product search"
                                onChange={(e) => setFilters({ ...filters, searchText: e.target.value.trim().toLowerCase() })}
                            />
                        </div>
                        <div className="flex flex-col sm:flex-row col-span-12 gap-[1.25rem]">
                            {data.filterOptions.map((filterItem, key) => {
                                if (filterItem.type === "dropdown") {
                                    const filterName = filterItem.name;
                                    let stateKey;
                                    let translationKey;

                                    switch (filterName) {
                                        case "metals-and-accessories":
                                            stateKey = "metalsAndAccessories";
                                            translationKey = translation.metals_accessories;
                                            break;
                                        case "color":
                                            stateKey = "color";
                                            translationKey = translation.colors;
                                            break;
                                        case "product_cat":
                                            stateKey = "productCat";
                                            translationKey = translation.product_category;
                                            break;
                                        default:
                                            // Handle unexpected cases
                                            stateKey = "";
                                            translationKey = "";
                                    }

                                    if (stateKey) {
                                        return (
                                            <div key={key} className="col-span-12 relative w-full max-w-full sm:max-w-64 mb-2.5">
                                                <label>{translationKey}</label>
                                                <select
                                                    value={filters[stateKey] || "none"} // Use the specific state key and default to "none" if undefined
                                                    onChange={(e) => handleTaxSelect(stateKey, e)}
                                                    className="select-filter block appearance-none w-full bg-white border border-gray-400 hover:border-gray-500 px-4 py-[0.95rem] pr-8 rounded shadow leading-tight focus:outline-none focus:shadow-outline text-[#737373] text-sm"
                                                >
                                                    <option value="none">Select {translationKey}</option>
                                                    {filterItem.tax_options.map((term, index) => {
                                                        return <option key={term.term_id} value={term.term_id}>{term.name}</option>
                                                    })}
                                                </select>
                                            </div>
                                        )
                                    }
                                }
                            })}

                        </div>
                   
                  
                <div className="container mb-5 col-span-12">
                    <button
                        type="button"
                        onClick={resetFilters}
                        className="text-black text-xs font-normal leading-tight"
                    >
                        Delete All Filters
                    </button>
                </div>

                <div className="flex flex-row gap-[1.25rem] col-span-12">
                    <div>
                        <Checkbox
                            label="Sample Available"
                            isChecked={filters.purchasability}
                            onChange={(isChecked) => setFilters(prevFilters => ({
                                ...prevFilters,
                                purchasability: isChecked
                            }))}
                        />

                    </div>
                    <div className="col-span-12 block mb-8">
                        <Checkbox
                            label="Online Available"
                            isChecked={filters.onlineAvailable}
                            onChange={(isChecked) => setFilters(prevFilters => ({
                                ...prevFilters,
                                onlineAvailable: isChecked
                            }))}
                        />
                    </div>
                </div>
                </div>
: null}
            </div>
            
            <div className="container mt-[54px]">
                <div className="grid grid-cols-3 mb-10 gap-y-14 sm:gap-[42px] filter-grid flex flex-wrap">
                    {!loading ?
                        originalDisplayedPosts.length ?
                            <>
                                {displayedPosts.map((post, index) => {
                                    return (
                                        <div key={index} className="flex flex-col h-full col-span-3 sm:col-span-1 gap-y-14 sm:gap-y-0 flex-grow" dangerouslySetInnerHTML={{ __html: atob(post.html) }}></div>
                                    )

                                })}
                            </>
                            :
                            <div className="w-full text-center">
                                {translation.no_results}
                            </div>
                        :
                        <div className="container flex justify-center">
                            <p>{translation.loading}</p>
                        </div>
                    }
                </div>
            </div>

            <div className="container flex justify-center items-center my-24 flex-col gap-y-6">
                {page < maxPages && displayedPosts.length < filteredPosts.length ? (
                    <button onClick={() => loadMorePosts()} disabled={displayedPosts.length >= originalDisplayedPosts.length} className="inline-flex items-center gap-x-2.5">
                        <svg xmlns="http://www.w3.org/2000/svg" width="50" height="50" viewBox="0 0 50 50" fill="none">
                            <path d="M25 4.6875C20.9826 4.6875 17.0554 5.87881 13.715 8.11077C10.3746 10.3427 7.77111 13.5151 6.23371 17.2267C4.6963 20.9384 4.29405 25.0225 5.07781 28.9628C5.86157 32.903 7.79615 36.5224 10.6369 39.3631C13.4777 42.2039 17.097 44.1384 21.0372 44.9222C24.9775 45.706 29.0616 45.3037 32.7733 43.7663C36.4849 42.2289 39.6573 39.6254 41.8892 36.285C44.1212 32.9446 45.3125 29.0174 45.3125 25C45.3068 19.6145 43.1649 14.4513 39.3568 10.6432C35.5487 6.83507 30.3855 4.69319 25 4.6875ZM25 42.1875C21.6006 42.1875 18.2776 41.1795 15.4511 39.2909C12.6247 37.4023 10.4217 34.718 9.12083 31.5774C7.81995 28.4368 7.47958 24.9809 8.14276 21.6469C8.80595 18.3128 10.4429 15.2503 12.8466 12.8466C15.2503 10.4429 18.3128 8.80594 21.6469 8.14275C24.9809 7.47957 28.4368 7.81994 31.5774 9.12082C34.718 10.4217 37.4023 12.6247 39.2909 15.4511C41.1795 18.2776 42.1875 21.6006 42.1875 25C42.1823 29.5568 40.3699 33.9255 37.1477 37.1477C33.9255 40.3699 29.5568 42.1823 25 42.1875ZM34.375 25C34.375 25.4144 34.2104 25.8118 33.9174 26.1049C33.6243 26.3979 33.2269 26.5625 32.8125 26.5625H26.5625V32.8125C26.5625 33.2269 26.3979 33.6243 26.1049 33.9174C25.8118 34.2104 25.4144 34.375 25 34.375C24.5856 34.375 24.1882 34.2104 23.8952 33.9174C23.6021 33.6243 23.4375 33.2269 23.4375 32.8125V26.5625H17.1875C16.7731 26.5625 16.3757 26.3979 16.0827 26.1049C15.7896 25.8118 15.625 25.4144 15.625 25C15.625 24.5856 15.7896 24.1882 16.0827 23.8951C16.3757 23.6021 16.7731 23.4375 17.1875 23.4375H23.4375V17.1875C23.4375 16.7731 23.6021 16.3757 23.8952 16.0826C24.1882 15.7896 24.5856 15.625 25 15.625C25.4144 15.625 25.8118 15.7896 26.1049 16.0826C26.3979 16.3757 26.5625 16.7731 26.5625 17.1875V23.4375H32.8125C33.2269 23.4375 33.6243 23.6021 33.9174 23.8951C34.2104 24.1882 34.375 24.5856 34.375 25Z" fill="black" />
                        </svg>
                        {translation.load_more}
                    </button>
                )
                    : null}
                <p class="text-base laeding-normal italic">{calculateDisplayedRange()}</p>
            </div>
        </div>
    )
}

export default Filter