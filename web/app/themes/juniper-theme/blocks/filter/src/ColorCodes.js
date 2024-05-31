class ColorCodeEntry {
    baseColor
    slugs

    constructor(base, slugs) {
        this.baseColor = base
        this.slugs = slugs
    }
}

class ColorCodes {
    /** @type ColorCodeEntry[]  */
    colorCodeEntries = []

    /**
     * @param entry {ColorCodeEntry}
     */
    addEntry(entry) {
        this.colorCodeEntries.push(entry)
    }

    /**
     * @param slug {String}
     * @returns {String}
     */
    getEntryWithSlug(slug) {
        const foundEntry = this.colorCodeEntries.find((entry) => entry.slug === slug)
        return foundEntry !== undefined ? foundEntry.baseColor : '#ffffff'
    }

    /**
     * @param slug {String}
     * @returns {String}
     */
    getEntryWithSlugLike(slug) {
        console.log(slug)

        const foundEntryParamSlugInc = this.colorCodeEntries.find((entry) => 
            entry.slugs.find(entrySlugs => slug.includes(entrySlugs)) !== undefined)
        const foundEntrySlugInc = this.colorCodeEntries.find((entry) => 
            entry.slugs.find(entrySlugs => entrySlugs.includes(slug)) !== undefined)

        const foundEntry = foundEntryParamSlugInc === undefined ? foundEntrySlugInc : foundEntryParamSlugInc

        return foundEntry !== undefined ? foundEntry.baseColor : '#ffffff'
    }
}


const codeEntries = new ColorCodes()

codeEntries.addEntry(new ColorCodeEntry('#faf8f4', [ "arega-pure-100", "arega-pure-380", "arega-pure-390", "white-arega-pure-100", "white-arega-pure-380", "white-arega-pure-390" ]))
codeEntries.addEntry(new ColorCodeEntry('#e9e4df', [ "rhodega-pure-c2", "rhodega-pure-k3", "white-rhodega-pure-c2", "white-rhodega-pure-k3" ]))
codeEntries.addEntry(new ColorCodeEntry('#e8e1d6', [ "rhodega-blend-pt", "rhodega-blend-ru", "rhodega-pure-for-pen-100", "white-rhodega-blend-pt", "white-rhodega-blend-ru", "white-rhodega-pure-for-pen-100" ]))
codeEntries.addEntry(new ColorCodeEntry('#dfd8ce', [ "platega-blend-rh", "platega-pure-k", "white-platega-blend-r", "white-platega-pure-k"]))
codeEntries.addEntry(new ColorCodeEntry('#ded8d0', [ "pallega-blend-co", "pallega-blend-f", "pallega-plend-n", "pallega-pure-f", "white-pallega-blend-c", "white-pallega-blend-f", "white-pallega-plend-n", "white-pallega-pure-f"]))
codeEntries.addEntry(new ColorCodeEntry('#d8d3ca', [ "pallega-pure-ec", "pallega-pure-h", "pallega-pure-t", "white-pallega-pure-e", "white-pallega-pure-h", "white-pallega-pure-t"]))
codeEntries.addEntry(new ColorCodeEntry('#bfbab3', [ "ruthega-pure-for-pen", "ruthega-pure-hs", "ruthega-pure-", "white-ruthega-pure-for-pe", "white-ruthega-pure-hs", "white-ruthega-pure-"]))
codeEntries.addEntry(new ColorCodeEntry('#9b9792', [ "rhodega-pure-black-100", "white-rhodega-pure-black-10"]))
codeEntries.addEntry(new ColorCodeEntry('#858380', [ "ruthega-pure-black-for-pen", "ruthega-pure-hs2-0-blac", "white-ruthega-pure-hs2-0-blac", "whiteruthega-pure-black-for-pe"]))
codeEntries.addEntry(new ColorCodeEntry('#f9f3e0', [ "0n", "aurega-for-pen-0n", "aurega-pure-240kp-0"]))
codeEntries.addEntry(new ColorCodeEntry('#eee1c2', [ "1n", "aurega-pure-1114m", "aurega-pure-111", "aurega-pure-11", "aurega-pure-1n1", "aurega-pure-1n14", "aurega-pure-218", "aurega-pure-240kp-1"]))
codeEntries.addEntry(new ColorCodeEntry('#e5ce99', [ "2n", "aurega-blend-cu-118c", "aurega-blend-pd-900", "aurega-for-pen-2", "aurega-pure-21", "aurega-pure-218", "aurega-pure-240kp-2", "aurega-pure-2n1", "aurega-pure-de"]))
codeEntries.addEntry(new ColorCodeEntry('#e9d49e', [ "3n", "aurega-for-pen-", "aurega-pure-210g", "aurega-pure-210", "aurega-pure-240kp-3", "aurega-pure-auroprin", "aurega-yellow-for-pen " ]))
codeEntries.addEntry(new ColorCodeEntry('#ebd2b7', [ "4n", "aurega-blend-pd-2001-t", "aurega-pure-240kp-4", "aurega-rose-1-for-pe", "aurega-rose-3-for-pe"]))
codeEntries.addEntry(new ColorCodeEntry('#efd0bd', [ "5n", "aurega-blend-cu-63", "aurega-blend-cu-64", "aurega-pure-240kp-5n"]))
codeEntries.addEntry(new ColorCodeEntry('#f0c8a7', [ "6n", "aurega-pure-240kp-6n"]))
codeEntries.addEntry(new ColorCodeEntry('#ddbc6a', [ "9n", "aurega-pure-240kp-9n"]))

export default codeEntries