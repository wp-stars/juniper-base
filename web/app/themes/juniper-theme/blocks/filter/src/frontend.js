import React from "react";
import ReactDOM from 'react-dom';
import Filter from "./components/Filter";
import FilterShop from "./components/FilterShop"; // Import the new component

const setupFilters = () => {
    // Handle Filter entries
    const filterDivs = document.querySelectorAll(".filter-entry");
    filterDivs.forEach(div => {
        let data = JSON.parse(div.dataset.initialData);
        const root = ReactDOM.createRoot(div);
        root.render(<Filter {...data} />);
        div.classList.remove("filter-entry");
    });

    // Handle FilterShop entries
    const filterShopDivs = document.querySelectorAll(".filter-entry-shop");
    filterShopDivs.forEach(div => {
        let data = JSON.parse(div.dataset.initialData);
        const root = ReactDOM.createRoot(div);
        root.render(<FilterShop {...data} />);
        div.classList.remove("filter-entry-shop");
    });
};

document.addEventListener('DOMContentLoaded', setupFilters);
