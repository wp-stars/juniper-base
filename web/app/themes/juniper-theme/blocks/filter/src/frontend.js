import React from "react";

import ReactDOM from 'react-dom';
import Filter from "./components/Filter";
import FilterShop from "./components/FilterShop"; 
import FilterNew from "./newComponents/Filter";

const setupFilters = () => {
    // Handle Filter entries
    // const filterDivs = document.querySelectorAll(".filter-entry");
    // filterDivs.forEach(div => {
    //     let data = JSON.parse(div.dataset.initialData);
    //     console.log(data)
    //     const root = ReactDOM.createRoot(div);
    //     root.render(<Filter {...data} />);
    //     div.classList.remove("filter-entry");
    // });

    const filterNew = document.querySelectorAll('.filter-entry')
    filterNew.forEach(div => {
        let data = JSON.parse(div.dataset.initialData);
        const root = ReactDOM.createRoot(div)
        root.render(<FilterNew {...data} />);
        div.classList.remove('filter-entry')
    })
};

document.addEventListener('DOMContentLoaded', setupFilters);