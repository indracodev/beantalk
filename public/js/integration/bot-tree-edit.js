/* Integrasi detail — bot-tree-edit. Data server: window.BT (lihat integration-detail.blade.php). */
// Drag and Drop Logic
function handleTreeDragStart(e, nodeId) {
    draggedNodeId = nodeId;
    e.dataTransfer.setData('text/plain', nodeId);
    e.dataTransfer.effectAllowed = 'move';
}

function handleTreeDragOver(e, targetNodeId) {
    e.preventDefault();
    if (draggedNodeId === targetNodeId) return;
    const targetCard = e.currentTarget;
    if (targetCard) {
        targetCard.classList.add('ring-2', 'ring-emerald-500', 'bg-emerald-50/40');
    }
}

function handleTreeDragLeave(e, targetNodeId) {
    const targetCard = e.currentTarget;
    if (targetCard) {
        targetCard.classList.remove('ring-2', 'ring-emerald-500', 'bg-emerald-50/40');
    }
}

function handleTreeDrop(e, targetNodeId) {
    e.preventDefault();
    const targetCard = e.currentTarget;
    if (targetCard) {
        targetCard.classList.remove('ring-2', 'ring-emerald-500', 'bg-emerald-50/40');
    }

    if (!draggedNodeId || draggedNodeId === targetNodeId) return;

    const draggedInfo = findNodeAndParent(currentBotTree, draggedNodeId);
    if (!draggedInfo) return;

    const isDescendant = (parent, checkId) => {
        if (!parent.children || parent.children.length === 0) return false;
        for (let c of parent.children) {
            if (c.id === checkId) return true;
            if (isDescendant(c, checkId)) return true;
        }
        return false;
    };

    if (isDescendant(draggedInfo.node, targetNodeId)) {
        alert('Tidak dapat memindahkan cabang ke dalam sub-cabangnya sendiri!');
        return;
    }

    // Unlink dragged node from current position
    const removedNode = draggedInfo.siblings.splice(draggedInfo.index, 1)[0];

    // Re-insert adjacent to target node
    const targetInfo = findNodeAndParent(currentBotTree, targetNodeId);
    if (targetInfo) {
        targetInfo.siblings.splice(targetInfo.index + 1, 0, removedNode);
    } else {
        currentBotTree.push(removedNode);
    }

    draggedNodeId = null;
    const searchVal = document.getElementById('inputSearchTree')?.value || '';
    renderDecisionTree(searchVal);
}

// Reorder Up / Down
function moveTreeNode(nodeId, direction) {
    const info = findNodeAndParent(currentBotTree, nodeId);
    if (!info) return;

    const siblings = info.siblings;
    const idx = info.index;

    if (direction === 'up' && idx > 0) {
        const temp = siblings[idx];
        siblings[idx] = siblings[idx - 1];
        siblings[idx - 1] = temp;
    } else if (direction === 'down' && idx < siblings.length - 1) {
        const temp = siblings[idx];
        siblings[idx] = siblings[idx + 1];
        siblings[idx + 1] = temp;
    }

    const searchVal = document.getElementById('inputSearchTree')?.value || '';
    renderDecisionTree(searchVal);
}

// Add New Root Node (Layer 1)
function addRootTreeNode() {
    const nextVal = (currentBotTree.length + 1).toString();
    const newNode = {
        id: 'node_' + Math.random().toString(36).substr(2, 7) + '_' + Date.now(),
        label: 'Menu Utama ' + nextVal,
        value: nextVal,
        response: 'Silakan pilih menu di bawah ini:',
        children: []
    };
    currentBotTree.push(newNode);
    renderDecisionTree();
    openEditTreeNodeModal(newNode.id);
}

// Add New Sub-Branch Node
function addChildTreeNode(parentId) {
    const info = findNodeAndParent(currentBotTree, parentId);
    if (!info) return;
    const parent = info.node;
    if (!Array.isArray(parent.children)) parent.children = [];

    const nextVal = (parent.value ? parent.value + '.' : '') + (parent.children.length + 1);
    const newChild = {
        id: 'node_' + Math.random().toString(36).substr(2, 7) + '_' + Date.now(),
        label: 'Sub-Pilihan ' + nextVal,
        value: nextVal,
        response: 'Berikut informasi untuk pilihan ini:',
        children: []
    };
    parent.children.push(newChild);
    collapsedNodes.delete(parentId);
    renderDecisionTree();
    openEditTreeNodeModal(newChild.id);
}

// Delete Node
function deleteTreeNode(nodeId) {
    if (!confirm('Apakah Anda yakin ingin menghapus cabang materi ini beserta seluruh sub-cabangnya?')) return;
    const info = findNodeAndParent(currentBotTree, nodeId);
    if (!info) return;
    info.siblings.splice(info.index, 1);
    const searchVal = document.getElementById('inputSearchTree')?.value || '';
    renderDecisionTree(searchVal);
}

// Open Edit Modal
function openEditTreeNodeModal(nodeId) {
    const info = findNodeAndParent(currentBotTree, nodeId);
    if (!info) return;
    const node = info.node;

    document.getElementById('modalEditNodeId').value = node.id;
    document.getElementById('modalEditNodeLabel').value = node.label || '';
    document.getElementById('modalEditNodeValue').value = node.value || '';
    document.getElementById('modalEditNodeResponse').value = node.response || '';

    const modal = document.getElementById('modalEditTreeNode');
    if (modal) modal.classList.remove('hidden');
    setTimeout(() => {
        document.getElementById('modalEditNodeLabel')?.focus();
    }, 50);
}

function closeEditTreeNodeModal() {
    const modal = document.getElementById('modalEditTreeNode');
    if (modal) modal.classList.add('hidden');
}

function saveEditedTreeNode() {
    const id = document.getElementById('modalEditNodeId')?.value;
    const label = document.getElementById('modalEditNodeLabel')?.value.trim();
    const val = document.getElementById('modalEditNodeValue')?.value.trim();
    const response = document.getElementById('modalEditNodeResponse')?.value.trim();

    if (!label) {
        alert('Label tombol menu tidak boleh kosong!');
        return;
    }

    const info = findNodeAndParent(currentBotTree, id);
    if (info) {
        info.node.label = label;
        info.node.value = val || label;
        info.node.response = response;
    }

    closeEditTreeNodeModal();
    const searchVal = document.getElementById('inputSearchTree')?.value || '';
    renderDecisionTree(searchVal);
}

// Expand / Collapse
function toggleTreeNodeCollapse(nodeId) {
    if (collapsedNodes.has(nodeId)) {
        collapsedNodes.delete(nodeId);
    } else {
        collapsedNodes.add(nodeId);
    }
    const searchVal = document.getElementById('inputSearchTree')?.value || '';
    renderDecisionTree(searchVal);
}

function expandAllTreeNodes() {
    collapsedNodes.clear();
    const searchVal = document.getElementById('inputSearchTree')?.value || '';
    renderDecisionTree(searchVal);
}

function collapseAllTreeNodes() {
    collapsedNodes.clear();
    const markCollapsed = (nodes) => {
        nodes.forEach(n => {
            if (n.children && n.children.length > 0) {
                collapsedNodes.add(n.id);
                markCollapsed(n.children);
            }
        });
    };
    markCollapsed(currentBotTree);
    const searchVal = document.getElementById('inputSearchTree')?.value || '';
    renderDecisionTree(searchVal);
}

function filterDecisionTree(query) {
    renderDecisionTree(query);
}

// Tree Synchronization & Compilation
function serializeBotTree() {
    // 1. Simpan struktur Tree utuh ke #hiddenBotTree
    const hiddenTree = document.getElementById('hiddenBotTree');
    if (hiddenTree) {
        hiddenTree.value = JSON.stringify(currentBotTree);
    }

    // 2. Ekstrak Root Level (Layer 1) menjadi Welcome Options
    const rootOptions = currentBotTree.map(n => ({
        label: n.label,
        value: n.value
    }));
    const hiddenWelcome = document.getElementById('hiddenBotWelcomeOptions');
    if (hiddenWelcome) {
        hiddenWelcome.value = JSON.stringify(rootOptions);
    }

    // 3. Compile Tree menjadi Flat Bot Rules (lengkap dengan navigasi opsi tombol)
    const treeRules = [];
    const compileNodeToRule = (node, parentNode = null) => {
        const options = [];
        if (node.children && node.children.length > 0) {
            node.children.forEach(c => {
                options.push({ label: c.label, value: c.value });
            });
            if (parentNode) {
                options.push({ label: '⬅️ Kembali', value: parentNode.value });
            }
            options.push({ label: '🏠 Menu Utama', value: 'MENU' });
        }

        const keywords = [node.value];
        if (node.label) {
            const cleanLabel = node.label.replace(/^[^\w\d]+/, '').trim().toLowerCase();
            if (cleanLabel && !keywords.includes(cleanLabel)) {
                keywords.push(cleanLabel);
            }
        }

        treeRules.push({
            name: node.label,
            keywords: keywords,
            response: node.response || '',
            options: options.length > 0 ? options : undefined
        });

        if (node.children && node.children.length > 0) {
            node.children.forEach(c => compileNodeToRule(c, node));
        }
    };

    currentBotTree.forEach(rootNode => compileNodeToRule(rootNode, null));

    // Tambahkan Menu Utama rule
    treeRules.unshift({
        name: 'Menu Utama',
        keywords: ['menu', 'menu utama', 'bantuan', 'help', 'mulai', 'start', 'halo', 'hai', 'pilihan'],
        response: document.querySelector('input[name="bot_welcome_message"]')?.value || 'Halo! Silakan pilih menu di bawah ini:',
        options: rootOptions
    });

    // Gabungkan dengan currentFaqRules (Mode Query FAQ)
    const combinedRules = [...treeRules];
    if (Array.isArray(currentFaqRules)) {
        currentFaqRules.forEach(faq => {
            if (faq.keywords && faq.response) {
                combinedRules.push(faq);
            }
        });
    }

    const hiddenRules = document.getElementById('hiddenBotRules');
    if (hiddenRules) {
        hiddenRules.value = JSON.stringify(combinedRules);
    }
}

