/* Integrasi detail — bot-tree. Data server: window.BT (lihat integration-detail.blade.php). */
// ============================================================
// 1.5 SMART BOT SUB-TABS & DECISION TREE MANAGER
// ============================================================

// Switch between Sub-Tab 1 (Query) and Sub-Tab 2 (Options)
function switchBotSubTab(subTab) {
    const queryPane = document.getElementById('pane-botsubtab-query');
    const optionsPane = document.getElementById('pane-botsubtab-options');
    const queryBtn = document.getElementById('btn-botsubtab-query');
    const optionsBtn = document.getElementById('btn-botsubtab-options');

    if (subTab === 'query') {
        if (queryPane) queryPane.style.display = 'flex';
        if (optionsPane) optionsPane.style.display = 'none';
        if (queryBtn) {
            queryBtn.className = 'px-3.5 py-2 rounded-lg text-[12px] font-semibold flex items-center gap-2 transition cursor-pointer bg-purple-600 text-white shadow-2xs';
        }
        if (optionsBtn) {
            optionsBtn.className = 'px-3.5 py-2 rounded-lg text-[12px] font-semibold flex items-center gap-2 transition cursor-pointer text-apple-textSecondary hover:text-apple-textPrimary hover:bg-apple-canvas';
        }
    } else {
        if (queryPane) queryPane.style.display = 'none';
        if (optionsPane) optionsPane.style.display = 'flex';
        if (optionsBtn) {
            optionsBtn.className = 'px-3.5 py-2 rounded-lg text-[12px] font-semibold flex items-center gap-2 transition cursor-pointer bg-emerald-600 text-white shadow-2xs';
        }
        if (queryBtn) {
            queryBtn.className = 'px-3.5 py-2 rounded-lg text-[12px] font-semibold flex items-center gap-2 transition cursor-pointer text-apple-textSecondary hover:text-apple-textPrimary hover:bg-apple-canvas';
        }
    }
}

// Update badges and dots for independent sub-tab toggles
function updateSubTabToggleStates() {
    const isQuery = Boolean(document.getElementById('toggleBotModeQuery')?.checked);
    const isOptions = Boolean(document.getElementById('toggleBotModeOptions')?.checked);

    const dotQuery = document.getElementById('dotQueryStatus');
    const dotOptions = document.getElementById('dotOptionsStatus');
    const badgeQuery = document.getElementById('badgeQueryActiveLabel');
    const badgeOptions = document.getElementById('badgeOptionsActiveLabel');

    if (dotQuery) {
        dotQuery.className = 'w-2 h-2 rounded-full ' + (isQuery ? 'bg-emerald-300 animate-pulse' : 'bg-neutral-300');
    }
    if (dotOptions) {
        dotOptions.className = 'w-2 h-2 rounded-full ' + (isOptions ? 'bg-emerald-300 animate-pulse' : 'bg-neutral-300');
    }

    if (badgeQuery) {
        badgeQuery.className = 'px-2 py-0.5 rounded-full text-[10.5px] font-bold ' + (isQuery ? 'bg-blue-100 text-blue-700' : 'bg-neutral-100 text-neutral-500');
        badgeQuery.textContent = isQuery ? 'Aktif (ON)' : 'Nonaktif (OFF)';
    }

    if (badgeOptions) {
        badgeOptions.className = 'px-2 py-0.5 rounded-full text-[10.5px] font-bold ' + (isOptions ? 'bg-emerald-100 text-emerald-700' : 'bg-neutral-100 text-neutral-500');
        badgeOptions.textContent = isOptions ? 'Aktif (ON)' : 'Nonaktif (OFF)';
    }

    updateBotModeBadge();
}

function updateBotModeBadge() {
    const isQuery = Boolean(document.getElementById('toggleBotModeQuery')?.checked);
    const isOptions = Boolean(document.getElementById('toggleBotModeOptions')?.checked);
    const badge = document.getElementById('badgeBotModeStatus');
    if (!badge) return;

    if (isQuery && isOptions) {
        badge.innerText = '⚡ Mode Hybrid (Query + Opsi)';
        badge.className = 'px-2.5 py-0.5 rounded-full text-[10.5px] font-bold bg-purple-100 text-purple-700';
    } else if (isOptions) {
        badge.innerText = '🔘 Hanya Mode Opsi';
        badge.className = 'px-2.5 py-0.5 rounded-full text-[10.5px] font-bold bg-emerald-100 text-emerald-800';
    } else if (isQuery) {
        badge.innerText = '💬 Hanya Mode Query';
        badge.className = 'px-2.5 py-0.5 rounded-full text-[10.5px] font-bold bg-blue-100 text-blue-800';
    } else {
        badge.innerText = '⚠️ Nonaktif (Hanya CS Handoff)';
        badge.className = 'px-2.5 py-0.5 rounded-full text-[10.5px] font-bold bg-neutral-100 text-neutral-600';
    }
}

// Tree Traversal & Search Helper
function findNodeAndParent(nodes, targetId, parent = null) {
    if (!Array.isArray(nodes)) return null;
    for (let i = 0; i < nodes.length; i++) {
        if (nodes[i].id === targetId) {
            return { node: nodes[i], parent: parent, index: i, siblings: nodes };
        }
        if (nodes[i].children && nodes[i].children.length > 0) {
            const res = findNodeAndParent(nodes[i].children, targetId, nodes[i]);
            if (res) return res;
        }
    }
    return null;
}

function countTotalNodes(nodes) {
    let count = 0;
    if (!Array.isArray(nodes)) return count;
    nodes.forEach(n => {
        count += 1;
        if (n.children && n.children.length > 0) {
            count += countTotalNodes(n.children);
        }
    });
    return count;
}

// Visual Decision Tree Renderer
function renderDecisionTree(filterText = '') {
    const container = document.getElementById('treeContainer');
    const emptyState = document.getElementById('treeEmptyState');
    const badgeStats = document.getElementById('badgeTreeStats');

    if (!container) return;
    container.innerHTML = '';

    const totalNodes = countTotalNodes(currentBotTree);
    if (badgeStats) badgeStats.innerText = totalNodes + ' Cabang';

    if (!Array.isArray(currentBotTree) || currentBotTree.length === 0) {
        if (emptyState) emptyState.classList.remove('hidden');
        serializeBotTree();
        return;
    }

    if (emptyState) emptyState.classList.add('hidden');

    const cleanFilter = filterText ? filterText.trim().toLowerCase() : '';

    function renderBranch(nodes, depth = 0, parentNode = null) {
        nodes.forEach((node, idx) => {
            const hasChildren = node.children && node.children.length > 0;
            const isCollapsed = collapsedNodes.has(node.id);
            
            // Check match filter
            const matchesThis = !cleanFilter || 
                (node.label && node.label.toLowerCase().includes(cleanFilter)) ||
                (node.value && node.value.toLowerCase().includes(cleanFilter)) ||
                (node.response && node.response.toLowerCase().includes(cleanFilter));

            let hasMatchingChild = false;
            if (cleanFilter && hasChildren) {
                const checkChildrenMatch = (ch) => {
                    for (let c of ch) {
                        if ((c.label && c.label.toLowerCase().includes(cleanFilter)) ||
                            (c.value && c.value.toLowerCase().includes(cleanFilter)) ||
                            (c.response && c.response.toLowerCase().includes(cleanFilter))) {
                            return true;
                        }
                        if (c.children && checkChildrenMatch(c.children)) return true;
                    }
                    return false;
                };
                hasMatchingChild = checkChildrenMatch(node.children);
            }

            if (cleanFilter && !matchesThis && !hasMatchingChild) {
                return;
            }

            const card = document.createElement('div');
            card.className = 'tree-node-item flex flex-col gap-1 transition select-none';
            card.id = 'tree-node-' + node.id;
            card.setAttribute('data-node-id', node.id);

            // Layer badge
            let layerBadge = '';
            if (depth === 0) {
                layerBadge = '<span class="px-2 py-0.5 rounded text-[10px] font-bold bg-purple-100 text-purple-800">Menu Utama (Lvl 1)</span>';
            } else if (depth === 1) {
                layerBadge = '<span class="px-2 py-0.5 rounded text-[10px] font-bold bg-blue-100 text-blue-800">Sub-Menu (Lvl 2)</span>';
            } else {
                layerBadge = `<span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800">Cabang (Lvl ${depth + 1})</span>`;
            }

            const indentMargin = depth * 28;
            const cleanResponse = (node.response || '').replace(/\s+/g, ' ').trim();
            const responsePreview = cleanResponse.length > 70 ? cleanResponse.substring(0, 70) + '...' : cleanResponse;

            const isFirst = idx === 0;
            const isLast = idx === nodes.length - 1;

            card.innerHTML = `
                <div class="tree-card relative flex flex-col md:flex-row items-start md:items-center justify-between gap-2.5 p-3 rounded-xl border border-apple-border bg-white shadow-2xs hover:border-emerald-400 transition"
                     style="margin-left: ${indentMargin}px;"
                     draggable="true"
                     ondragstart="handleTreeDragStart(event, '${node.id}')"
                     ondragover="handleTreeDragOver(event, '${node.id}')"
                     ondragleave="handleTreeDragLeave(event, '${node.id}')"
                     ondrop="handleTreeDrop(event, '${node.id}')">
                    
                    ${depth > 0 ? `<div class="absolute -left-4 top-1/2 -translate-y-1/2 w-4 h-0.5 bg-neutral-300 pointer-events-none"></div>` : ''}

                    <div class="flex items-center gap-2.5 flex-1 min-w-0">
                        <span class="cursor-grab active:cursor-grabbing text-neutral-400 hover:text-neutral-700 font-mono text-[14px] px-1 py-0.5 rounded hover:bg-neutral-100" title="Seret untuk memindahkan posisi / urutan">⠿</span>
                        
                        ${hasChildren ? `
                            <button type="button" onclick="toggleTreeNodeCollapse('${node.id}')" class="w-5 h-5 flex items-center justify-center rounded hover:bg-neutral-100 text-neutral-500 font-mono text-[10px] cursor-pointer" title="${isCollapsed ? 'Buka Sub-Cabang' : 'Tutup Sub-Cabang'}">
                                ${isCollapsed ? '▶' : '▼'}
                            </button>
                        ` : `
                            <span class="w-5 h-5 flex items-center justify-center text-neutral-300 text-[10px]">&bull;</span>
                        `}

                        <div class="flex flex-col gap-0.5 min-w-0">
                            <div class="flex items-center gap-2 flex-wrap">
                                ${layerBadge}
                                <span class="font-bold text-[12.5px] text-apple-textPrimary truncate">${escapeHtml(node.label || 'Tanpa Label')}</span>
                                <span class="px-1.5 py-0.2 rounded bg-apple-canvas text-neutral-600 font-mono text-[10.5px] border border-apple-border" title="Trigger Key: ${escapeHtml(node.value || '')}">${escapeHtml(node.value || '-')}</span>
                                ${hasChildren ? `<span class="px-1.5 py-0.2 rounded-full bg-emerald-50 text-emerald-700 text-[10px] font-semibold">${node.children.length} Sub-Pilihan</span>` : ''}
                            </div>
                            ${responsePreview ? `
                                <div class="text-[11px] text-apple-textSecondary flex items-center gap-1 truncate max-w-xl">
                                    <span class="text-neutral-400">💬</span>
                                    <span class="truncate">${escapeHtml(responsePreview)}</span>
                                </div>
                            ` : ''}
                        </div>
                    </div>

                    <div class="flex items-center gap-1.5 shrink-0 self-end md:self-center">
                        <button type="button" onclick="addChildTreeNode('${node.id}')" class="inline-flex items-center gap-1 px-2 py-1 rounded-lg bg-emerald-50 hover:bg-emerald-100 text-emerald-700 font-semibold text-[11px] border border-emerald-200 transition cursor-pointer" title="Tambah Sub-Cabang ke menu ini">
                            <span>➕ Sub-Cabang</span>
                        </button>

                        <button type="button" onclick="openEditTreeNodeModal('${node.id}')" class="inline-flex items-center gap-1 px-2 py-1 rounded-lg bg-apple-canvas/70 hover:bg-apple-canvas text-apple-textPrimary font-semibold text-[11px] border border-apple-border transition cursor-pointer" title="Edit Teks & Jawaban">
                            <span>✏️ Edit</span>
                        </button>

                        <div class="flex items-center rounded-lg border border-apple-border bg-apple-canvas/40 overflow-hidden">
                            <button type="button" onclick="moveTreeNode('${node.id}', 'up')" ${isFirst ? 'disabled' : ''} class="px-1.5 py-1 text-[10px] text-neutral-600 hover:bg-white disabled:opacity-30 disabled:hover:bg-transparent transition cursor-pointer" title="Geser ke Atas">▲</button>
                            <span class="w-[1px] h-3 bg-apple-border"></span>
                            <button type="button" onclick="moveTreeNode('${node.id}', 'down')" ${isLast ? 'disabled' : ''} class="px-1.5 py-1 text-[10px] text-neutral-600 hover:bg-white disabled:opacity-30 disabled:hover:bg-transparent transition cursor-pointer" title="Geser ke Bawah">▼</button>
                        </div>

                        <button type="button" onclick="deleteTreeNode('${node.id}')" class="p-1 rounded-lg text-neutral-400 hover:text-red-600 hover:bg-red-50 transition cursor-pointer" title="Hapus Cabang Ini">
                            <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                        </button>
                    </div>
                </div>
            `;

            container.appendChild(card);

            if (hasChildren && (!isCollapsed || (cleanFilter && hasMatchingChild))) {
                renderBranch(node.children, depth + 1, node);
            }
        });
    }

    renderBranch(currentBotTree, 0, null);
    serializeBotTree();
}

