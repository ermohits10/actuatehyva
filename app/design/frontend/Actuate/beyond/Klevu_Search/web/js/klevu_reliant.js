/* ====================================================== */
/* ====================================================== */
/* ======= KLEVU TEMPLATE JS CUSTOMIZATION CODE ========= */
/* ============ LANDING & CATNAV JAVASCRIPT ============= */
/* ====================================================== */
/* ====================================================== */

// Create the useroptions object if it does not already exist
if (!window.klevu_uc_userOptions) window.klevu_uc_userOptions = {};

// set the landingFilterPosition
window.klevu_uc_userOptions.landingFilterPosition = 'left';

const myPriceFormatters = {
    GBP: {
        appendCurrencyAtLast: false,
        currencySymbol: "£",
        decimalPlaces: 0,
        decimalSeparator: ".",
        thousandSeparator: "",
        format: "%s%s",
        grouping: 3,
    },
}

if (typeof klevu_uc_userOptions === "undefined") {
    klevu_uc_userOptions = {}
}

function getPriceFormatter(currencyCode) {
    const defaultCurrency = "EUR"

    if (typeof currencyCode === "string" && myPriceFormatters.hasOwnProperty(currencyCode)) {
        return myPriceFormatters[currencyCode]
    } else {
        return myPriceFormatters[defaultCurrency]
    }
}

klevu_uc_userOptions.priceFormatter = getPriceFormatter(klevu_currentCurrencyCode);

klevu.beforeActivation("landing", function (data, scope) {

    scope.kScope.template.setTemplate(klevuLandingTemplateFiltersCustom, 'filters', true);
    scope.kScope.template.setTemplate(klevuLandingTemplateResultsCustom, 'results', true);
    scope.kScope.template.setTemplate(landingPageProductAddToCartCustom, 'landingPageProductAddToCart', true);
    scope.kScope.template.setTemplate(klevuLandingTemplateProductBlockCustom, 'productBlock', true);

    scope.kScope.chains.events.keyUp.remove({ name: "scrollToTop" });

    (function (klevu) {
        klevu.extend(true, klevu.search.modules, {
            addParamsToURL: {
                base: {
                    addURLtoHistory: function (data, scope, queryId) {
                        // Global stuff
                        if (typeof window.history === "undefined" || typeof window.history.pushState === "undefined") {
                            console.error("This browser does not have the support of window.history or window.history.pushState");
                            return;
                        }

                        var searchPath = new URLSearchParams(window.location.search);
                        var currentSearchPath;
                        var previousSearchPath = searchPath.toString();

                        // this is in case we're running our code where we dont have context.activeQueryId
                        var activeQueryId = klevu.getObjectPath(data, "context.activeQueryId");
                        if (queryId && queryId.length) {
                            activeQueryId = queryId;
                        }

                        // Check if there any filter selected
                        let applyFilters = typeof data.localOverrides.query[activeQueryId].filters !== 'undefined' ? data.localOverrides.query[activeQueryId].filters.applyFilters : {};
                        let isThereFiltersSelected = false;
                        // Check if applyFilters is an object (but not an array), and not empty
                        if (typeof applyFilters === 'object' && !Array.isArray(applyFilters) && applyFilters !== null && Object.keys(applyFilters).length > 0) {
                            isThereFiltersSelected = true;
                        }

                        // PAGINATION TO URL
                        var hasPaginationEnabled = klevu.getSetting(klevu, "settings.theme.modules.pagination.searchResultsPage.enable");
                        var paginationQueryParam = "Page";
                        var currentPage = 0;

                        if (hasPaginationEnabled) {
                            var activeQueryMeta = klevu.getObjectPath(data, "template.query." + activeQueryId + ".meta");
                            if (activeQueryMeta) {
                                var productListLimit = activeQueryMeta.noOfResults;
                                currentPage = Math.ceil(activeQueryMeta.offset / productListLimit) + 1;
                            }

                            if (currentPage === 0 || currentPage === 1) {
                                searchPath.delete(paginationQueryParam);
                            } else {
                                searchPath.set(paginationQueryParam, currentPage);
                            }

                            currentSearchPath = searchPath.toString();
                        };

                        // FILTERS TO URL
                        var activeQueryFilters = klevu.getObjectPath(data, "template.query." + activeQueryId + ".filters");
                        var filterValuesQueryParam = "";

                        if (activeQueryFilters) {
                            klevu.each(activeQueryFilters, function (key, filter) {
                                var selectedFilterKeyString = "";
                                var filterOptions = filter.options;
                                var selectedValues = "";

                                if (filterOptions && filterOptions.length) {
                                    klevu.each(filterOptions, function (key, option) {
                                        if (option.selected === true) {
                                            if (selectedValues.length) {
                                                selectedValues += ",";
                                            }
                                            selectedValues += option.value;
                                        }
                                    });
                                } else if (filter.type === "SLIDER") {
                                    var startValue = filter.start;
                                    var endValue = filter.end;
                                    var minValue = filter.min;
                                    var maxValue = filter.max;
                                    if (typeof startValue !== "undefined" && startValue !== null && typeof endValue !== "undefined" && endValue !== null) {
                                        if (Number(startValue) === Number(minValue) && Number(endValue) === Number(maxValue)) {
                                            // just skipping
                                        } else {
                                            selectedValues = startValue + "-" + endValue;
                                        }
                                    }
                                }

                                if (selectedValues.length) {
                                    // selectedFilterKeyString += filter.key + ":" + selectedValues;
                                    selectedFilterKeyString += encodeURIComponent(filter.key) + ":" + encodeURIComponent(selectedValues);
                                }

                                if (selectedFilterKeyString.length) {
                                    if (filterValuesQueryParam.length) {
                                        filterValuesQueryParam += ";";
                                    }
                                    filterValuesQueryParam += selectedFilterKeyString;
                                }
                            });
                        }

                        if (filterValuesQueryParam.length > 0 && isThereFiltersSelected) {
                            searchPath.set("Filters", filterValuesQueryParam);
                        } else {
                            searchPath.delete("Filters");
                        }

                        currentSearchPath = searchPath.toString();

                        // ==============================
                        // Final URL change
                        if (previousSearchPath !== currentSearchPath) {
                            var historyState = { page: currentPage, filters: currentSearchPath };
                            window.history.pushState(historyState, null, currentSearchPath ? '?' + currentSearchPath : window.location.pathname);
                        }
                    },

                    restoreSearchParamsOnPageLoad: function (data, scope) {
                        var hasAlreadyTriggered = klevu.getObjectPath(scope.kScope, "element.kScope.getAndUpdateFiltersTriggered");
                        if (hasAlreadyTriggered) return;

                        var activeQueryId = klevu.getObjectPath(data, "context.activeQueryId") || "productList";

                        var filtersFromURL = klevu.dom.helpers.getQueryStringValue("Filters");
                        var paginationFromURL = klevu.dom.helpers.getQueryStringValue("Page");

                        var matchedQueryParamId = "";
                        var matchedQueryParamValue = "";
                        var recordQueries = klevu.getObjectPath(data, "request.current.recordQueries");

                        if (recordQueries && recordQueries.length) {
                            klevu.each(recordQueries, function (key, recordQuery) {
                                if (recordQuery.id) {
                                    if ((paginationFromURL && paginationFromURL.length) || (filtersFromURL && filtersFromURL.length)) {
                                        matchedQueryParamId = recordQuery.id;
                                        matchedQueryParamValue = paginationFromURL || filtersFromURL;
                                    }
                                }
                            });
                        }

                        klevu.setObjectPath(scope.kScope, "element.kScope.getAndUpdateFiltersTriggered", true);

                        matchedQueryParamId = activeQueryId || "productList";

                        if (matchedQueryParamId.length && matchedQueryParamValue.length) {
                            activeQueryId = matchedQueryParamId;
                            var storage = klevu.getSetting(scope.kScope.settings, "settings.storage");
                            if (storage.tabs) {
                                storage.tabs.setStorage("local");
                                storage.tabs.mergeFromGlobal();
                                storage.tabs.addElement("active", activeQueryId);
                                storage.tabs.mergeToGlobal();
                            }
                        }

                        if (paginationFromURL && paginationFromURL > 1) {
                            if (recordQueries && recordQueries.length) {
                                klevu.each(recordQueries, function (key, recordQuery) {
                                    if (recordQuery.id === activeQueryId) {
                                        var limit = klevu.getObjectPath(recordQuery, "settings.limit");
                                        limit = Number(limit);
                                        if (limit > 0) {
                                            var expectedOffset = (paginationFromURL - 1) * limit;
                                            klevu.setObjectPath(data, "localOverrides.query." + activeQueryId + ".settings.offset", expectedOffset);
                                        }
                                    }
                                });
                            }
                        }

                        // FILTERS
                        if (filtersFromURL && filtersFromURL.length) {
                            var facets = filtersFromURL.split(";");
                            if (facets) {
                                klevu.each(facets, function (key, facet) {
                                    var splitFacet = facet.split(":");
                                    if (splitFacet.length) {
                                        var facetKey = decodeURIComponent(splitFacet[0]);
                                        var facetValues = decodeURIComponent(splitFacet[1]).split(",");
                                        var applyFilters = klevu.getObjectPath(data, "localOverrides.query." + activeQueryId + ".filters.applyFilters.filters");

                                        if (applyFilters && applyFilters.length) { // if there're some filters
                                            var isExistingKey = false;
                                            klevu.each(applyFilters, function (key, applyFilter) {
                                                if (applyFilter.key === facetKey) {
                                                    isExistingKey = true;
                                                    klevu.each(facetValues, function (key, facetValue) {
                                                        var isFilterOptionMatched = false;
                                                        klevu.each(applyFilter.values, function (key, value) {
                                                            if (facetValue === value) {
                                                                isFilterOptionMatched = true;
                                                            }
                                                        });
                                                        if (!isFilterOptionMatched) {
                                                            applyFilter.values.push(facetValue);
                                                        }
                                                    });
                                                }
                                            });
                                            if (!isExistingKey) {
                                                applyFilters.push({ key: facetKey, values: facetValues });
                                            }
                                        } else {
                                            klevu.setObjectPath(data, "localOverrides.query." + activeQueryId + ".filters.applyFilters.filters", [{ key: facetKey, values: facetValues }]);
                                        }

                                    }
                                });
                            }
                        }
                    },

                    updatePayloadOnPopState: function (data, scope, activeQueryId) {
                        // SET PAGINATION PAYLOAD
                        var paginationFromURL = klevu.dom.helpers.getQueryStringValue("Page");
                        paginationFromURL = Number(paginationFromURL);
                        var limit = window.klevuCurrentScope.element.kObject.getScope().data.request.original.queryRequest[0].settings.limit;
                        limit = Number(limit);
                        if (limit > 0) {
                            var expectedOffset = ((paginationFromURL - 1) * limit) > 0 ? ((paginationFromURL - 1) * limit) : 0;
                            klevu.setObjectPath(data, "localOverrides.query." + activeQueryId + ".settings.offset", expectedOffset);
                            // klevu.search.modules.addParamsToURL.base.backBtnCounter.backBtn = 0;
                        }

                        // SET FILTER PAYLOAD
                        var filtersFromURL = klevu.dom.helpers.getQueryStringValue("Filters");
                        if (filtersFromURL && filtersFromURL.length) {
                            var facets = filtersFromURL.split(";");
                            // loop through all facets to get its values
                            let facetKey = '';
                            let facetValues = [];
                            let facetsArray = [];

                            klevu.each(facets, function (key, facet) {
                                var splitFacet = facet.split(":");
                                if (splitFacet.length) {
                                    facetKey = decodeURIComponent(splitFacet[0]);
                                    facetValues = decodeURIComponent(splitFacet[1]).split(",");
                                    facetsArray.push({ key: facetKey, values: facetValues });
                                }
                            });
                            // set the payload with correct filters:
                            klevu.setObjectPath(data, "localOverrides.query." + activeQueryId + ".filters.applyFilters.filters", facetsArray);
                        } else { // reset filters if Back button pressed, and there's no filters
                            klevu.setObjectPath(data, "localOverrides.query." + activeQueryId + ".filters.applyFilters.filters", []);
                        }
                    }
                },
                build: true
            },
            filterOpener: function (data, scope) {
                var filterOpener = document.querySelector('.ku-facet-opener');
                var filterClose = document.querySelector('.ku-facet-close');
                var filterApply = document.querySelector('.kuFiltersMobButton_apply');
                var landingElement = document.querySelector('.klevuLanding');
                if (filterOpener && landingElement) {
                    klevu.event.attach(filterOpener, "click", function (event) {
                        document.querySelector('.klevuLanding ').classList.toggle('ku-facet-open');
                    });
                }
                if (filterClose && landingElement) {
                    klevu.event.attach(filterClose, "click", function (event) {
                        document.querySelector('.klevuLanding ').classList.toggle('ku-facet-open');
                    });
                }
                if (filterApply && landingElement) {
                    klevu.event.attach(filterApply, "click", function (event) {
                        document.querySelector('.klevuLanding ').classList.toggle('ku-facet-open');
                    });
                }
            },
            insertToTopButton: function (data, scope) {
                let topButton = document.getElementById('ku-back-to-top');
                if (!topButton) {
                    topButton = document.createElement('button');
                    topButton.id = 'ku-back-to-top';
                    topButton.title = 'Back to top';
                    topButton.className = 'ku-hide';
                    topButton.textContent = '↑';
                    document.body.appendChild(topButton);
                }

            },
            addBodyClassOnScroll: function () {
                // Helper function to get the top position of an element
                function getElementYPosition () {
                    var element = document.querySelector('.kuResultsListing');
                    if (element) {
                        var boundingClientRect = element.getBoundingClientRect();
                        var absoluteElementTop = boundingClientRect.top + window.pageYOffset;
                        return absoluteElementTop;
                    }
                    return null; // Return null if the element doesn't exist
                };
                window.addEventListener('scroll', function (e) {
                    const positionY = window.scrollY;
                    const productListPositionY = getElementYPosition();
                    const triggerPositionY = productListPositionY !== null ? productListPositionY : 100;
                    const body = document.body;
                    const shouldAddClass = positionY >= triggerPositionY;
                    body.classList.toggle('klevu-sticky', shouldAddClass);
                });
            },
            smoothScrollToProductListingTop: function () {
                let customOffset = 60;
                const selectorsToTriggerScrollToTop = {
                    pagination: '.kuPagination a',
                    scrollToTopButton: '#ku-back-to-top'
                  // filterOptions: '.klevuFilterOption',
                  // Add more selectors here as needed
                }
                function _smoothScrollToKlevuTop() {
                  setTimeout(function () {
                      var target = document.getElementsByClassName('klevuMeta')[0];

                      if (target) {
                          var topPosition = target.getBoundingClientRect().top + window.pageYOffset;

                          window.scrollTo({
                              top: topPosition - customOffset,
                              behavior: 'smooth'
                          });
                      }
                  }, 100);
                }
                Object.values(selectorsToTriggerScrollToTop).forEach(selector => {
                  document.querySelectorAll(selector).forEach(el => el.addEventListener('click', _smoothScrollToKlevuTop));
                });
            },
            keepFacetsWithSelectedOptionsOpened: function (data, scope) {
                const selectors = {
                    kuFilterBox: '.kuFilterBox',
                    kuSelected: '.kuSelected',
                    kuFilterNames: '.kuFilterNames',
                    kuFilterNamesCollapse: '.kuFilterNames.kuFilterCollapse',
                    kuFilterHead: '.kuFilterHead',
                    kuFilterHeadExpand: '.kuFilterHead.kuExpand',
                    kuExpand: '.kuExpand',
                    kuCollapse: '.kuCollapse',
                    nouiElement: '.noUi-origin',
                    priceFacetBlock: '[data-filter="klevu_price"]',
                    kuFilterShowAll: '.kuFilterShowAll',
                    kuFilterCollapse: '.kuFilterCollapse',
                };

                /**
                (function collapseAllFilters() {
                    // Replace .kuFilterHead.kuCollapse with .kuFilterHead.kuExpand
                    const kuFilterHeadElements = document.querySelectorAll(selectors.kuFilterHead);
                    kuFilterHeadElements.forEach(element => {
                        element.classList.add(selectors.kuExpand.substring(1));
                        element.classList.remove(selectors.kuCollapse.substring(1));
                    });

                    // Replace .kuFilterNames.kuCollapse with .kuFilterNames.kuExpand
                    const kuFilterNamesElements = document.querySelectorAll(selectors.kuFilterNames);
                    kuFilterNamesElements.forEach(element => {
                        element.classList.add(selectors.kuFilterCollapse.substring(1));
                        element.classList.remove(selectors.kuFilterShowAll.substring(1));
                    });
                }());
                */

                // Regular checkbox-facets
                // Check if the required elements exist
                const kuSelectedElements = document.querySelectorAll(selectors.kuFilterBox + ' ' + selectors.kuSelected);
                if (kuSelectedElements.length > 0) {
                    kuSelectedElements.forEach(selectedElement => {
                        // Find the parent element with the class kuFilterBox
                        const filterBoxParent = findParentWithClass(selectedElement, selectors.kuFilterBox);

                        // Look for the child with the class kuFilterHead.kuExpand
                        const filterHeadChild = filterBoxParent.querySelector(selectors.kuFilterHeadExpand);

                        // Rotate icon in the title
                        if (filterHeadChild) {
                            filterHeadChild.classList.remove('kuExpand');
                            filterHeadChild.classList.add('kuCollapse');
                        }

                        // Find the parent element with the class kuFilterNames.kuFilterCollapse
                        const filterNamesParent = findParentWithClass(selectedElement, selectors.kuFilterNamesCollapse);

                        // Show options
                        if (filterNamesParent) {
                            filterNamesParent.classList.remove('kuFilterCollapse');
                            filterNamesParent.classList.add(selectors.kuFilterShowAll.substring(1));
                        }
                    });
                }
                    // Price slider facet
                    var isThereAnyAppliedFacets = klevu.getObjectPath(
                        scope.kScope.data,
                        "localOverrides.query." +
                        scope.kScope.data.context.activeQueryId +
                        ".filters.applyFilters.filters"
                    );
                    const isPriceFacetApplied = Boolean(isThereAnyAppliedFacets?.filter(val => val.key === "klevu_price").length);
                    if (isPriceFacetApplied) {
                    // Find the parent element with the class kuFilterNames.kuFilterCollapse
                    const nouiElement = document.querySelector(`${selectors.priceFacetBlock} ${selectors.nouiElement}`);
                    if (nouiElement) {
                        const filterNamesParent = findParentWithClass(nouiElement, selectors.kuFilterNamesCollapse);
                        // Show options
                        if (filterNamesParent) {
                            filterNamesParent.classList.remove('kuFilterCollapse');
                            filterNamesParent.classList.add(selectors.kuFilterShowAll.substring(1));
                        }
                        // Find the parent element with the class kuFilterBox
                        const filterBoxParent = findParentWithClass(nouiElement, selectors.kuFilterBox);
                        // Look for the child with the class kuFilterHead.kuExpand
                        const filterHeadChild = filterBoxParent.querySelector(selectors.kuFilterHeadExpand);
                        // Rotate icon in the title
                        if (filterHeadChild) {
                            filterHeadChild.classList.remove('kuExpand');
                            filterHeadChild.classList.add('kuCollapse');
                        }
                    }
                }
                // Function-helper to find the parent element with a specified class
                function findParentWithClass(element, className) {
                    let parent = element.parentElement;
                    while (parent) {
                        if (parent.matches(className)) {
                            return parent;
                        }
                        parent = parent.parentElement;
                    }
                    return null; // No matching parent found
                }
            }
        });
    })(klevu);

})

// Apply the facets supplied in the current URL string
klevu.modifyRequest("landing", function (data, scope) {

    if (localStorage.getItem('klv_sort_productList') !== null) {
        localStorage.removeItem('klv_sort_productList');
    }

    klevu.each(data.request.current.recordQueries, function (key, query) { query.filters.filtersToReturn.options.limit = 21; });

    // Restore Search Params On Page Load
    klevu.search.modules.addParamsToURL.base.restoreSearchParamsOnPageLoad(data, scope);

    // set the global scope
    window.klevuCurrentScope = scope.kScope;
});

klevu.afterTemplateRender("landing", function (data, scope) {
    klevu.search.modules.addParamsToURL.base.addURLtoHistory(data, scope);
    klevu.search.modules.insertToTopButton(data, scope);
    klevu.search.modules.filterOpener(data, scope);
    klevu.search.modules.addBodyClassOnScroll();
    klevu.search.modules.keepFacetsWithSelectedOptionsOpened(data, scope);
    klevu.search.modules.smoothScrollToProductListingTop(data, scope);
});

window.onpopstate = function (event) {
    try {
        // passing activeQueryId because it's lost once we reset the data below
        var activeQueryId = klevu.getObjectPath(window.klevuCurrentScope.element.kObject.getScope().data, "context.activeQueryId");
        // code to get ready to reset all the data
        var data = window.klevuCurrentScope.element.kObject.getScope().data;
        var scope = window.klevuCurrentScope.element;
        scope.kScope.data = scope.kObject.resetData(scope.kElem);
        scope.kScope.data.context.keyCode = 0;
        scope.kScope.data.context.eventObject = event;
        scope.kScope.data.context.event = "keyUp";
        scope.kScope.data.context.preventDefault = false;

        // Get payload for Filters & Pagination
        klevu.search.modules.addParamsToURL.base.updatePayloadOnPopState(data, scope, activeQueryId);

        // Fire request, re-render results
        klevu.event.fireChain(scope.kScope, "chains.events.keyUp", scope, scope.kScope.data, event);
    } catch (error) {
        console.log(error)
    }
};


var klevuLandingTemplateFiltersCustom = `
    <% if(data.query[dataLocal].filters.length > 0 ) { %>
        <div class="kuFilters" role="navigation" data-position="left" aria-label="Product Filters" tabindex="0" x-data="{ expanded: false }">
            <div class="kuFiltersTitle block-title relative b-left block border border-[#000d40] rounded-md p-3 md:border-0 md:roudned-none md:w-full md:bg-transparent md:pt-0 md:py-0 md:px-0 md:my-0 cursor-pointer" @click="expanded = ! expanded">
                <h3 class="kuFiltersTitleHeading block-title h-5 md:h-10 flex items-center justify-center md:justify-between md:border-b md:border-gray-200 md:pb-5"><span class="text-sm leading-5 md:text-xl md:leading-7 font-sans md:font-mono font-medium lg:font-semibold md:mb-2"><%=helper.translate("Filters")%></span></h3>
            </div>
            <div class="block-content kuFiltersContent filter-content clear-both" x-show="expanded">
                <div class="flex flex-wrap w-full">
                    <% helper.each(data.query[dataLocal].filters,function(key,filter){ %>
                        <% if (filter.key.toLowerCase() === 'category' || filter.key.toLowerCase() === 'manufacturer' || filter.key.toLowerCase() === 'klevu_price') { %>
                            <% filter.isCollapsed = false; %>
                        <% } else { %>
                            <% filter.isCollapsed = true; %>
                        <% } %>

                        <% if(filter.type == "OPTIONS"){ %>
                            <div class="kuFilterBox w-full klevuFilter card mt-[1px] border-b border-gray-200 <%=(filter.multiselect)?'kuMulticheck':''%>" data-filter="<%=filter.key%>" <% if(filter.multiselect){ %> data-singleselect="false" <% } else { %> data-singleselect="true"<% } %>>
                                <div class="kuFilterHead flex justify-between items-center cursor-pointer hover:text-secondary-darker border-container pb-4 !border-none !border-0 !font-sans !text-base !leading-6 !font-medium !capitalize <%=(filter.isCollapsed) ? 'kuExpand' : 'kuCollapse'%>" style="border: none">
                                    <span class="title !font-sans !text-base !leading-6 !font-medium mb-4"><% var filter_label = (filter.label=="klevu_price") ? "price" : filter.label; %></span>
                                    <%=helper.translate(filter_label)%>
                                </div>
                                <div data-optionCount="<%= filter.options.length %>" class="kuFilterNames <%=(filter.isCollapsed) ? 'kuFilterCollapse' : ''%> <%= filter.options.length >= 7 ? 'optional' : '' %>">
                                    <ul>
                                        <% helper.each(filter.options,function(key,filterOption){ %>
                                            <li <% if(filterOption.selected ==true) { %> class="kuSelected"<% } %>>
                                                <a
                                                    target="_self"
                                                    href="#"
                                                    title="<%=helper.escapeHTML(filterOption.name)%>"
                                                    class="klevuFilterOption<% if(filterOption.selected ==true) { %> klevuFilterOptionActive<% } %>"
                                                    data-value="<%=helper.escapeHTML(filterOption.value)%>"
                                                    data-name="<%=helper.escapeHTML(filterOption.name)%>"
                                                >
                                                    <span class="kuFilterIcon"></span>
                                                    <span class="kufacet-text"><%=filterOption.name%></span>
                                                    <% if(filterOption.selected ==true) { %>
                                                        <span class="kuFilterCancel">X</span>
                                                    <% } else { %>
                                                        <span class="kuFilterTotal"><%=filterOption.count%></span>
                                                    <% } %>
                                                </a>
                                            </li>
                                        <%  }); %>
                                    </ul>
                                    <% if(filter.options.length >= 7 ) { %>
                                        <div class="kuShowOpt" tabindex="-1">
                                            <span class="kuFilterDot"></span><span class="kuFilterDot"></span><span class="kuFilterDot"></span>
                                        </div>
                                    <% } %>
                                </div>
                            </div>
                        <% } else if(filter.type == "SLIDER")  { %>
                            <div class="kuFilterBox w-full klevuFilter card mt-[1px] border-b border-gray-200" data-filter="<%=filter.key%>">
                                <div class="kuFilterHead flex justify-between items-center cursor-pointer hover:text-secondary-darker border-container pb-4 !border-none !border-0 !font-sans !text-base !leading-6 !font-medium !capitalize <%=(filter.isCollapsed) ? 'kuExpand' : 'kuCollapse'%>" style="border: none">
                                    <span class="title !font-sans !text-base !leading-6 !font-medium mb-4"><% var filter_label = (filter.label=="klevu_price") ? "price" : filter.label; %></span>
                                    <%=helper.translate(filter_label)%>
                                </div>
                                <div class="kuFilterNames sliderFilterNames <%=(filter.isCollapsed) ? 'kuFilterCollapse' : ''%>">
                                    <div class="kuPriceSlider klevuSliderFilter" data-query = "<%=dataLocal%>">
                                        <div data-querykey = "<%=dataLocal%>" class="noUi-target noUi-ltr noUi-horizontal noUi-background kuSliderFilter kuPriceRangeSliderFilter<%=dataLocal%>"></div>
                                    </div>
                                </div>
                            </div>
                        <% } else if (filter.type == "RATING")  { %>
                            <div class="kuFilterBox w-full klevuFilter card mt-[1px] border-b border-gray-200 <%=(filter.multiselect)?'kuMulticheck':''%>" data-filter="<%=filter.key%>" <% if(filter.multiselect){ %> data-singleselect="false" <% } else { %> data-singleselect="true"<% } %>>
                                <div class="kuFilterHead flex justify-between items-center cursor-pointer hover:text-secondary-darker border-container pb-4 !border-none !border-0 !font-sans !text-base !leading-6 !font-medium !capitalize <%=(filter.isCollapsed) ? 'kuExpand' : 'kuCollapse'%>" style="border: none">
                                    <span class="title !font-sans !text-base !leading-6 !font-medium mb-4"><%=helper.translate(filter.label)%><span>
                                </div>
                                <div data-optionCount="<%= filter.options.length %>" class="kuFilterNames <%= (filter.options.length <= 5 ) ? 'kuFilterShowAll': '' %> <%=(filter.isCollapsed) ? 'kuFilterCollapse' : ''%>">
                                    <ul>
                                        <% helper.each(filter.options,function(key,filterOption){ %>
                                            <li <% if(filterOption.selected ==true) { %> class="kuSelected"<% } %>>
                                                <a
                                                    target="_self"
                                                    href="#"
                                                    title="<%=helper.escapeHTML(filterOption.name)%>"
                                                    class="klevuFilterOption<% if(filterOption.selected ==true) { %> klevuFilterOptionActive<% } %>"
                                                    data-value="<%=helper.escapeHTML(filterOption.value)%>"
                                                    data-name="<%=helper.escapeHTML(filterOption.name)%>"
                                                >
                                                    <span class="kuFilterIcon"></span>
                                                    <span class="kufacet-text">
                                                        <div class="klevuFacetStars">
                                                            <div class="klevuFacetRating" style="width:<%=(20*Number(filterOption.name))%>%;"></div>
                                                        </div>
                                                    </span>
                                                    <% if(filterOption.selected ==true) { %>
                                                        <span class="kuFilterCancel">X</span>
                                                    <% } else { %>
                                                        <span class="kuFilterTotal"><%=filterOption.count%></span>
                                                    <% } %>
                                                </a>
                                            </li>
                                        <%  }); %>
                                    </ul>
                                </div>
                            </div>
                        <% } else { %>
                            <!-- Other Facets -->
                        <% } %>
                    <% }); %>
                    <div class="kuFiltersMobButtons md:hidden">
                        <button class="kuFiltersMobButton kuFiltersMobButton_apply" tabindex="0"><%= helper.translate("Apply")%></button>
                        <button class="kuFiltersMobButton kuFiltersMobButton_reset kuFilterTagClearAll" tabindex="0">Reset</button>
                    </div>
                </div>
            </div>
        </div>
    <% } %>
`;

var klevuLandingTemplateResultsCustom = `
    <div class="kuResultsListing">
        <div class="productList klevuMeta" data-section="productList">
            <div class="kuResultContent">
                <div class="kuResultWrap <%=(data.query.productList.filters.length == 0 )?'kuBlockFullwidth':''%>">
                    <div ku-container data-container-id="ku_landing_main_content_container" data-container-role="content">
                        <section ku-container data-container-id="ku_landing_main_content_left" data-container-position="left" data-container-role="left">
                            <div ku-block data-block-id="ku_landing_left_facets">
                                <%=helper.render('filters',scope,data,"productList") %>
                            </div>
                            <div ku-block data-block-id="ku_landing_left_call_outs"></div>
                            <div ku-block data-block-id="ku_landing_left_banner"></div>
                        </section>
                        <section ku-container data-container-id="ku_landing_main_content_center" data-container-position="center" data-container-role="center">
                            <header ku-block data-block-id="ku_landing_result_header">
                                <%
                                    var lastPageResults = (Number(data.query.productList.meta.noOfResults) + Number(data.query.productList.meta.offset)) > data.query.productList.meta.totalResultsFound ? data.query.productList.meta.totalResultsFound : (Number(data.query.productList.meta.noOfResults) + Number(data.query.productList.meta.offset));
                                    var results = ((Number(data.query.productList.meta.offset) !== 0) ? Number(data.query.productList.meta.offset) : '') + ' - ' + lastPageResults;
                                    if(results > data.query.productList.meta.totalResultsFound){
                                        results = data.query.productList.meta.totalResultsFound;
                                    }
                                %>
                                <%=helper.render('klevuLandingTemplateResultsHeadingTitle',scope,data,"productList") %>
                                <%=helper.render('filtersTop',scope,data,"productList") %>
                                <%= helper.render('kuFilterTagsTemplate',scope,data,"productList") %>
                                <% if(helper.hasResults(data,"productList")) { %>
                                    <%=helper.render('sortBy',scope,data,"productList") %>
                                    <%=helper.render('limit',scope,data,"productList") %>
                                    <div class="ku-numberOfResults">Showing <%= results %> of <%=data.query.productList.meta.totalResultsFound %></div>

                                    <%=helper.render('kuTemplateLandingResultsViewSwitch',scope,data,"productList") %>
                                <% } %>
                                <div class="kuClearBoth"></div>
                            </header>

                            <div ku-block data-block-id="ku_landing_result_items">
                                <div class="kuResults">
                                    <% if(helper.hasResults(data,"productList")) { %>
                                        <ul>
                                            <% helper.each(data.query.productList.result,function(key,item){ %>
                                                <% if(item.typeOfRecord == "KLEVU_PRODUCT") { %>
                                                    <%=helper.render('productBlock',scope,data,item) %>
                                                <% } %>
                                            <% }); %>
                                        </ul>
                                    <% } else { %>
                                        <div class="kuNoRecordsFoundLabelTextContainer">
                                            <span class="kuNoRecordsFoundLabelText"><%= helper.translate("No records found for your selection") %></span>
                                        </div>
                                    <% } %>
                                    <div class="kuClearBoth"></div>
                                </div>
                            </div>
                            <% if(helper.hasResults(data,"productList")) { %>
                                <%=helper.render('pagination',scope,data,"productList") %>
                            <% } %>
                            <div ku-block data-block-id="ku_landing_other_items">
                                <%=helper.render('klevuLandingTemplateInfiniteScrollDown',scope,data) %>
                            </div>
                            <footer ku-block data-block-id="ku_landing_result_footer"></footer>
                        </section>
                        <section ku-container data-container-id="ku_landing_main_content_right" data-container-position="right" data-container-role="right">
                            <div ku-block data-block-id="ku_landing_right_facets"></div>
                            <div ku-block data-block-id="ku_landing_right_call_outs"></div>
                            <div ku-block data-block-id="ku_landing_right_banner"></div>
                        </section>
                    </div>
                </div>
            </div>
        </div>

        <% if(data.query.contentList) { %>
            <div class="contentList klevuMeta" data-section="contentList" data-result-view="list">
                <div class="kuResultContent">
                    <div class="kuResultWrap <%=(data.query.contentList.filters.length == 0 )?'kuBlockFullwidth':''%>">
                        <div ku-container data-container-id="ku_landing_main_content_container" data-container-role="content">
                            <section ku-container data-container-id="ku_landing_main_content_left" data-container-position="left" data-container-role="left">
                                <div ku-block data-block-id="ku_landing_left_facets">
                                    <%=helper.render('filters',scope,data,"contentList") %>
                                </div>
                                <div ku-block data-block-id="ku_landing_left_call_outs"></div>
                                <div ku-block data-block-id="ku_landing_left_banner"></div>
                            </section>
                            <section ku-container data-container-id="ku_landing_main_content_center" data-container-position="center" data-container-role="center">
                                <header ku-block data-block-id="ku_landing_result_header">
                                    <%=helper.render('filtersTop',scope,data,"contentList") %>
                                    <%= helper.render('kuFilterTagsTemplate',scope,data,"contentList") %>
                                    <% if(helper.hasResults(data,"contentList")) { %>
                                        <%=helper.render('limit',scope,data,"contentList") %>
                                        <%=helper.render('pagination',scope,data,"contentList") %>
                                    <% } %>
                                    <div class="kuClearBoth"></div>
                                </header>

                                <div ku-block data-block-id="ku_landing_result_items">
                                    <div class="kuClearBoth"></div>
                                    <div class="kuResults">
                                        <% if(helper.hasResults(data,"contentList")) { %>
                                            <ul>
                                                <% helper.each(data.query.contentList.result,function(key,item){ %>
                                                    <% if(item.typeOfRecord == "KLEVU_CMS") { %>
                                                        <%=helper.render('contentBlock',scope,data,item) %>
                                                    <% }%>
                                                <% }); %>
                                            </ul>
                                        <% } else { %>
                                            <div class="kuNoRecordsFoundLabelTextContainer">
                                                <span class="kuNoRecordsFoundLabelText"><%= helper.translate("No records found for your selection") %></span>
                                            </div>
                                        <% } %>
                                        <div class="kuClearBoth"></div>
                                    </div>
                                </div>
                                <div ku-block data-block-id="ku_landing_other_items">
                                    <%=helper.render('klevuLandingTemplateInfiniteScrollDown',scope,data) %>
                                </div>
                                <footer ku-block data-block-id="ku_landing_result_footer"></footer>
                            </section>
                            <section ku-container data-container-id="ku_landing_main_content_right" data-container-position="right" data-container-role="right">
                                <div ku-block data-block-id="ku_landing_right_facets"></div>
                                <div ku-block data-block-id="ku_landing_right_call_outs"></div>
                                <div ku-block data-block-id="ku_landing_right_banner"></div>
                            </section>
                        </div>
                    </div>
                </div>
            </div>
        <% } %>
    </div>
    <script>
        function initLayeredNavigation() {
            return {
                isMobile: false,
                blockOpen: false,
                checkIsMobileResolution() {
                    this.isMobile = window.matchMedia('(max-width: 768px)').matches;
                }
            }
        }
    </script>
`;

var klevuLandingTemplateProductBlockCustom = `
    <%
        var updatedProductName = dataLocal.name;
        if(klevu.search.modules.kmcInputs.base.getSkuOnPageEnableValue()) {
            if(klevu.dom.helpers.cleanUpSku(dataLocal.sku)) {
                updatedProductName += klevu.dom.helpers.cleanUpSku(dataLocal.sku);
            }
        }
    %>
    <li ku-product-block class="klevuProduct" data-id="<%=dataLocal.id%>">
        <div class="kuProdWrap">
            <header ku-block data-block-id="ku_landing_result_item_header">
                <%=helper.render('landingProductBadge', scope, data, dataLocal) %>
            </header>
            <% var desc = [dataLocal.summaryAttribute,dataLocal.packageText,dataLocal.summaryDescription].filter(function(el) { return el; }); desc = desc.join(" "); %>
            <main ku-block data-block-id="ku_landing_result_item_info">
                <div class="kuProdTop">
                    <div class="klevuImgWrap">
                        <a data-id="<%=dataLocal.id%>" href="<%=dataLocal.url%>" class="klevuProductClick kuTrackRecentView">
                            <img src="<%=dataLocal.image%>" origin="<%=dataLocal.image%>" onerror="klevu.dom.helpers.cleanUpProductImage(this)" alt="<%=updatedProductName%>" class="kuProdImg">
                            <%=helper.render('landingImageRollover', scope, data, dataLocal) %>
                        </a>
                    </div>
                    <!-- <div class="kuQuickView">
                        <button data-id="<%=dataLocal.id%>" class="kuBtn kuBtnLight kuQuickViewBtn" role="button" tabindex="0" area-label="">Quick view</button>
                    </div> -->
                </div>
            </main>
            <footer ku-block="" data-block-id="ku_landing_result_item_footer">
                <div class="kuProdBottom">
                    <div class="kuName kuClippedOne"><a data-id="<%=dataLocal.id%>" href="<%=dataLocal.url%>" class="klevuProductClick kuTrackRecentView" title="<%= updatedProductName %>"><%= updatedProductName %></a></div>
                    <% if(dataLocal.inStock && dataLocal.inStock != "yes") { %>
                        <%=helper.render('landingProductStock', scope, data, dataLocal) %>
                    <% } else { %>
                    <% if(klevu.search.modules.kmcInputs.base.getShowPrices()) { %>
                        <div class="kuPrice">
                            <%
                                var kuTotalVariants = klevu.dom.helpers.cleanUpPriceValue(dataLocal.totalVariants);
                                var kuStartPrice = klevu.dom.helpers.cleanUpPriceValue(dataLocal.startPrice,dataLocal.currency);
                                var kuSalePrice = klevu.dom.helpers.cleanUpPriceValue(dataLocal.salePrice,dataLocal.currency);
                                var kuPrice = klevu.dom.helpers.cleanUpPriceValue(dataLocal.price,dataLocal.currency);
                            %>
                            <% if(!Number.isNaN(kuTotalVariants) && !Number.isNaN(kuStartPrice)) { %>
                                <% if(!Number.isNaN(kuStartPrice) && !Number.isNaN(kuPrice) && (kuPrice > kuStartPrice)) { %>
                                    <span class="kuOrigPrice kuClippedOne">
                                        RRP <%= helper.processCurrency(dataLocal.currency,parseFloat(dataLocal.price)) %>
                                    </span>
                                <% } %>
                                <span class="kuSalePrice kuSpecialPrice kuClippedOne"><%=helper.processCurrency(dataLocal.currency,parseFloat(dataLocal.startPrice)) %></span>
                            <% } else if(!Number.isNaN(kuSalePrice) && !Number.isNaN(kuPrice) && (kuPrice > kuSalePrice)){ %>
                                <span class="kuOrigPrice kuClippedOne">
                                    RRP <%= helper.processCurrency(dataLocal.currency,parseFloat(dataLocal.price)) %>
                                </span>
                                <span class="kuSalePrice kuSpecialPrice kuClippedOne">
                                    <%=helper.processCurrency(dataLocal.currency,parseFloat(dataLocal.salePrice))%>
                                </span>
                            <% } else if(!Number.isNaN(kuSalePrice)) { %>
                                <span class="kuSalePrice kuSpecialPrice">
                                    <%= helper.processCurrency(dataLocal.currency,parseFloat(dataLocal.salePrice)) %>
                                </span>
                            <% } else if(!Number.isNaN(kuPrice)) { %>
                                <span class="kuSalePrice">
                                    <%= helper.processCurrency(dataLocal.currency,parseFloat(dataLocal.price)) %>
                                </span>
                            <% } %>
                            <%=helper.render('searchResultProductVATLabel', scope, data, dataLocal) %>
                        </div>
                    <% } %>
                    <% } %>
                </div>
                <div class="kuProdAdditional">
                    <div class="kuProdAdditionalData">
                        <% if(desc && desc.length) { %>
                            <div class="kuDesc kuClippedTwo"> <%=desc%> </div>
                        <% } %>
                        <%=helper.render('landingProductSwatch',scope,data,dataLocal) %>
                        <%=helper.render('klevuLandingProductRating',scope,data,dataLocal) %>
                        <% var isAddToCartEnabled = klevu.search.modules.kmcInputs.base.getAddToCartEnableValue(); %>
                        <% if(isAddToCartEnabled) { %>
                            <%=helper.render('landingPageProductAddToCart',scope,data,dataLocal) %>
                        <% } %>
                    </div>
                </div>
             </footer>
        </div>
    </li>
`;

var landingPageProductAddToCartCustom = `
    <a class="w-full btn btn-primary justify-center text-sm mr-auto" href="<%=dataLocal.url%>" aria-label="View Product">
        <span class="inline">View Product</span>
    </a>
`;
