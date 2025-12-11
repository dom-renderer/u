@extends('layouts.app-master')

@push('css')
<style>
    /* Main Container */
    .tree-container {
        position: relative;
        width: 100%;
        height: calc(100vh - 200px);
        min-height: 600px;
        background: linear-gradient(135deg, #1a1a2e 0%, #16213e 50%, #0f3460 100%);
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
    }

    /* SVG Styles */
    .tree-svg {
        width: 100%;
        height: 100%;
        cursor: grab;
    }

    .tree-svg:active {
        cursor: grabbing;
    }

    /* Node Styles */
    .node {
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .node:hover {
        filter: brightness(1.2);
    }

    .node-rect {
        rx: 12;
        ry: 12;
        transition: all 0.3s ease;
        filter: drop-shadow(0 4px 6px rgba(0, 0, 0, 0.3));
    }

    .node-rect-entry {
        fill: url(#entryGradient);
        stroke: #ffd93d;
        stroke-width: 3;
    }

    .node-rect-regular {
        fill: url(#regularGradient);
        stroke: #4ade80;
        stroke-width: 2;
    }

    .node:hover .node-rect {
        filter: drop-shadow(0 8px 12px rgba(0, 0, 0, 0.4));
        transform: scale(1.02);
    }

    .node-text {
        fill: #ffffff;
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
        font-weight: 600;
        text-anchor: middle;
        pointer-events: none;
    }

    .node-step-badge {
        fill: rgba(255, 255, 255, 0.2);
        rx: 4;
        ry: 4;
    }

    .node-step-text {
        fill: #ffffff;
        font-size: 10px;
        font-weight: 700;
        text-anchor: middle;
    }

    .node-section-text {
        fill: rgba(255, 255, 255, 0.7);
        font-size: 10px;
        text-anchor: middle;
    }

    /* Link Styles */
    .link {
        fill: none;
        stroke: url(#linkGradient);
        stroke-width: 2.5;
        opacity: 0.8;
        transition: all 0.3s ease;
    }

    .link:hover {
        stroke-width: 4;
        opacity: 1;
    }

    .link-arrow {
        fill: #4ade80;
    }

    /* Control Panel */
    .control-panel {
        position: absolute;
        top: 20px;
        right: 20px;
        display: flex;
        flex-direction: column;
        gap: 8px;
        z-index: 100;
    }

    .control-btn {
        width: 44px;
        height: 44px;
        border: none;
        border-radius: 12px;
        background: rgba(255, 255, 255, 0.1);
        backdrop-filter: blur(10px);
        color: #ffffff;
        font-size: 18px;
        cursor: pointer;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.2);
    }

    .control-btn:hover {
        background: rgba(255, 255, 255, 0.2);
        transform: translateY(-2px);
        box-shadow: 0 6px 12px rgba(0, 0, 0, 0.3);
    }

    .control-btn:active {
        transform: translateY(0);
    }

    /* Legend Panel */
    .legend-panel {
        position: absolute;
        bottom: 20px;
        left: 20px;
        background: rgba(255, 255, 255, 0.1);
        backdrop-filter: blur(10px);
        border-radius: 12px;
        padding: 16px;
        z-index: 100;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.2);
    }

    .legend-title {
        color: #ffffff;
        font-size: 12px;
        font-weight: 600;
        margin-bottom: 12px;
        text-transform: uppercase;
        letter-spacing: 1px;
    }

    .legend-item {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 8px;
    }

    .legend-color {
        width: 24px;
        height: 24px;
        border-radius: 6px;
    }

    .legend-color-entry {
        background: linear-gradient(135deg, #ffd93d 0%, #ff9500 100%);
        border: 2px solid #ffd93d;
    }

    .legend-color-step {
        background: linear-gradient(135deg, #0d5d31 0%, #327350 100%);
        border: 2px solid #4ade80;
    }

    .legend-text {
        color: rgba(255, 255, 255, 0.9);
        font-size: 13px;
    }

    /* Details Panel */
    .details-panel {
        position: absolute;
        top: 20px;
        left: 20px;
        width: 320px;
        max-height: calc(100% - 40px);
        background: rgba(255, 255, 255, 0.1);
        backdrop-filter: blur(20px);
        border-radius: 16px;
        padding: 20px;
        z-index: 100;
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
        transform: translateX(-120%);
        transition: transform 0.3s ease;
        overflow-y: auto;
    }

    .details-panel.show {
        transform: translateX(0);
    }

    .details-close {
        position: absolute;
        top: 12px;
        right: 12px;
        width: 28px;
        height: 28px;
        border: none;
        border-radius: 8px;
        background: rgba(255, 255, 255, 0.1);
        color: #ffffff;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.3s ease;
    }

    .details-close:hover {
        background: rgba(255, 255, 255, 0.2);
    }

    .details-header {
        margin-bottom: 16px;
        padding-bottom: 16px;
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    }

    .details-step-badge {
        display: inline-block;
        padding: 4px 12px;
        background: rgba(255, 255, 255, 0.2);
        border-radius: 20px;
        color: #ffffff;
        font-size: 11px;
        font-weight: 600;
        margin-bottom: 8px;
    }

    .details-title {
        color: #ffffff;
        font-size: 18px;
        font-weight: 700;
        margin: 0;
    }

    .details-section {
        color: rgba(255, 255, 255, 0.7);
        font-size: 12px;
        margin-top: 4px;
    }

    .details-content {
        display: flex;
        flex-direction: column;
        gap: 12px;
    }

    .details-row {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
    }

    .details-label {
        color: rgba(255, 255, 255, 0.6);
        font-size: 12px;
        font-weight: 500;
    }

    .details-value {
        color: #ffffff;
        font-size: 13px;
        font-weight: 500;
        text-align: right;
        max-width: 180px;
    }

    .details-badge {
        display: inline-block;
        padding: 2px 8px;
        border-radius: 4px;
        font-size: 11px;
        font-weight: 600;
    }

    .badge-entry {
        background: linear-gradient(135deg, #ffd93d 0%, #ff9500 100%);
        color: #000000;
    }

    .badge-auto {
        background: rgba(59, 130, 246, 0.3);
        color: #93c5fd;
    }

    .badge-manual {
        background: rgba(251, 191, 36, 0.3);
        color: #fcd34d;
    }

    /* Zoom Info */
    .zoom-info {
        position: absolute;
        bottom: 20px;
        right: 20px;
        background: rgba(255, 255, 255, 0.1);
        backdrop-filter: blur(10px);
        border-radius: 8px;
        padding: 8px 16px;
        color: rgba(255, 255, 255, 0.7);
        font-size: 12px;
        z-index: 100;
    }

    /* Header Info */
    .tree-header {
        background: linear-gradient(135deg, #0d5d31 0%, #327350 100%);
        color: white;
        padding: 20px 24px;
        border-radius: 12px;
        margin-bottom: 20px;
        box-shadow: 0 4px 15px rgba(13, 93, 49, 0.3);
    }

    .tree-header-title {
        font-size: 1.5rem;
        font-weight: 700;
        margin-bottom: 4px;
    }

    .tree-header-subtitle {
        opacity: 0.9;
        font-size: 0.9rem;
    }

    .tree-actions {
        display: flex;
        gap: 10px;
    }

    .btn-back {
        background: rgba(255, 255, 255, 0.2);
        border: none;
        color: white;
        padding: 8px 16px;
        border-radius: 8px;
        font-weight: 500;
        transition: all 0.3s ease;
        text-decoration: none;
    }

    .btn-back:hover {
        background: rgba(255, 255, 255, 0.3);
        color: white;
    }

    /* Loading State */
    .loading-overlay {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(26, 26, 46, 0.9);
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        z-index: 200;
    }

    .loading-spinner {
        width: 48px;
        height: 48px;
        border: 4px solid rgba(255, 255, 255, 0.1);
        border-left-color: #4ade80;
        border-radius: 50%;
        animation: spin 1s linear infinite;
    }

    @keyframes spin {
        to { transform: rotate(360deg); }
    }

    .loading-text {
        color: rgba(255, 255, 255, 0.7);
        margin-top: 16px;
        font-size: 14px;
    }

    /* Empty State */
    .empty-state {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        text-align: center;
        color: rgba(255, 255, 255, 0.7);
    }

    .empty-state-icon {
        font-size: 48px;
        margin-bottom: 16px;
        opacity: 0.5;
    }

    .empty-state-text {
        font-size: 16px;
    }

    /* Tooltip */
    .tree-tooltip {
        position: absolute;
        background: rgba(0, 0, 0, 0.9);
        backdrop-filter: blur(10px);
        color: #ffffff;
        padding: 10px 14px;
        border-radius: 8px;
        font-size: 12px;
        pointer-events: none;
        z-index: 300;
        transform: translate(-50%, -100%);
        margin-top: -10px;
        white-space: nowrap;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
    }

    .tree-tooltip::after {
        content: '';
        position: absolute;
        top: 100%;
        left: 50%;
        transform: translateX(-50%);
        border: 6px solid transparent;
        border-top-color: rgba(0, 0, 0, 0.9);
    }

    /* Minimap */
    .minimap-container {
        position: absolute;
        bottom: 60px;
        right: 20px;
        width: 180px;
        height: 120px;
        background: rgba(0, 0, 0, 0.4);
        border-radius: 8px;
        overflow: hidden;
        border: 1px solid rgba(255, 255, 255, 0.1);
        z-index: 100;
    }

    .minimap-viewport {
        fill: rgba(74, 222, 128, 0.2);
        stroke: #4ade80;
        stroke-width: 1;
    }
</style>
@endpush

@section('content')
<div class="p-4">
    <!-- Header -->
    <div class="tree-header d-flex justify-content-between align-items-center">
        <div>
            <div class="tree-header-title">{{ $assignment->title }}</div>
            <div class="tree-header-subtitle">
                Workflow Tree Visualization • {{ $assignment->children->count() }} steps
            </div>
        </div>
        <div class="tree-actions">
            <a href="{{ route('workflow-assignments.show', encrypt($assignment->id)) }}" class="btn-back">
                <i class="bi bi-arrow-left me-2"></i>Back to Details
            </a>
        </div>
    </div>

    <!-- Tree Container -->
    <div class="tree-container" id="treeContainer">
        <!-- Loading Overlay -->
        <div class="loading-overlay" id="loadingOverlay">
            <div class="loading-spinner"></div>
            <div class="loading-text">Loading workflow tree...</div>
        </div>

        <!-- SVG will be rendered here -->
        <svg class="tree-svg" id="treeSvg">
            <defs>
                <!-- Gradients -->
                <linearGradient id="entryGradient" x1="0%" y1="0%" x2="100%" y2="100%">
                    <stop offset="0%" style="stop-color:#ffd93d"/>
                    <stop offset="100%" style="stop-color:#ff9500"/>
                </linearGradient>
                <linearGradient id="regularGradient" x1="0%" y1="0%" x2="100%" y2="100%">
                    <stop offset="0%" style="stop-color:#0d5d31"/>
                    <stop offset="100%" style="stop-color:#327350"/>
                </linearGradient>
                <linearGradient id="linkGradient" x1="0%" y1="0%" x2="100%" y2="0%">
                    <stop offset="0%" style="stop-color:#4ade80"/>
                    <stop offset="100%" style="stop-color:#22d3ee"/>
                </linearGradient>
                <!-- Arrow Marker -->
                <marker id="arrowhead" viewBox="0 -5 10 10" refX="8" refY="0"
                        markerWidth="6" markerHeight="6" orient="auto">
                    <path d="M0,-5L10,0L0,5" fill="#4ade80"/>
                </marker>
            </defs>
            <g id="treeGroup"></g>
        </svg>

        <!-- Control Panel -->
        <div class="control-panel">
            <button class="control-btn" id="zoomIn" title="Zoom In">
                <i class="bi bi-plus-lg"></i>
            </button>
            <button class="control-btn" id="zoomOut" title="Zoom Out">
                <i class="bi bi-dash-lg"></i>
            </button>
            <button class="control-btn" id="zoomReset" title="Reset View">
                <i class="bi bi-arrows-angle-contract"></i>
            </button>
            <button class="control-btn" id="zoomFit" title="Fit to Screen">
                <i class="bi bi-fullscreen"></i>
            </button>
        </div>

        <!-- Details Panel -->
        <div class="details-panel" id="detailsPanel">
            <button class="details-close" id="detailsClose">
                <i class="bi bi-x"></i>
            </button>
            <div class="details-header">
                <span class="details-step-badge" id="detailsStepBadge">S1</span>
                <h3 class="details-title" id="detailsTitle">Step Name</h3>
                <div class="details-section" id="detailsSection">Section Name</div>
            </div>
            <div class="details-content" id="detailsContent">
                <!-- Dynamic content -->
            </div>
        </div>

        <!-- Legend Panel -->
        <div class="legend-panel">
            <div class="legend-title">Legend</div>
            <div class="legend-item">
                <div class="legend-color legend-color-entry"></div>
                <span class="legend-text">Entry Point (Start)</span>
            </div>
            <div class="legend-item">
                <div class="legend-color legend-color-step"></div>
                <span class="legend-text">Workflow Step</span>
            </div>
        </div>

        <!-- Zoom Info -->
        <div class="zoom-info" id="zoomInfo">
            Zoom: 100%
        </div>

        <!-- Tooltip -->
        <div class="tree-tooltip" id="treeTooltip" style="display: none;"></div>
    </div>
</div>
@endsection

@push('js')
<script src="https://d3js.org/d3.v7.min.js"></script>
<script>
$(document).ready(function() {
    const templateId = '{{ encrypt($assignment->id) }}';
    const dataUrl = '{{ route("workflow-assignments.tree-data", encrypt($assignment->id)) }}';
    
    // Configuration
    const config = {
        nodeWidth: 200,
        nodeHeight: 80,
        nodePadding: 40,
        levelHeight: 150
    };

    // State
    let treeData = null;
    let currentZoom = 1;
    let simulation = null;

    // DOM Elements
    const container = document.getElementById('treeContainer');
    const svg = d3.select('#treeSvg');
    const treeGroup = d3.select('#treeGroup');
    const loadingOverlay = document.getElementById('loadingOverlay');
    const detailsPanel = document.getElementById('detailsPanel');
    const tooltip = document.getElementById('treeTooltip');
    const zoomInfo = document.getElementById('zoomInfo');

    // Get container dimensions
    function getDimensions() {
        const rect = container.getBoundingClientRect();
        return { width: rect.width, height: rect.height };
    }

    // Zoom behavior
    const zoom = d3.zoom()
        .scaleExtent([0.1, 4])
        .on('zoom', (event) => {
            treeGroup.attr('transform', event.transform);
            currentZoom = event.transform.k;
            zoomInfo.textContent = `Zoom: ${Math.round(currentZoom * 100)}%`;
        });

    svg.call(zoom);

    // Zoom controls
    document.getElementById('zoomIn').addEventListener('click', () => {
        svg.transition().duration(300).call(zoom.scaleBy, 1.3);
    });

    document.getElementById('zoomOut').addEventListener('click', () => {
        svg.transition().duration(300).call(zoom.scaleBy, 0.7);
    });

    document.getElementById('zoomReset').addEventListener('click', () => {
        svg.transition().duration(300).call(zoom.transform, d3.zoomIdentity);
    });

    document.getElementById('zoomFit').addEventListener('click', fitToScreen);

    // Close details panel
    document.getElementById('detailsClose').addEventListener('click', () => {
        detailsPanel.classList.remove('show');
    });

    // Fetch and render tree data
    fetchTreeData();

    async function fetchTreeData() {
        try {
            const response = await fetch(dataUrl);
            treeData = await response.json();
            
            loadingOverlay.style.display = 'none';
            
            if (treeData.nodes.length === 0) {
                showEmptyState();
                return;
            }

            renderTree();
            setTimeout(fitToScreen, 100);
        } catch (error) {
            console.error('Error fetching tree data:', error);
            loadingOverlay.innerHTML = `
                <div class="empty-state-icon"><i class="bi bi-exclamation-triangle"></i></div>
                <div class="loading-text">Error loading workflow data</div>
            `;
        }
    }

    function showEmptyState() {
        treeGroup.append('text')
            .attr('class', 'empty-state')
            .attr('x', getDimensions().width / 2)
            .attr('y', getDimensions().height / 2)
            .attr('text-anchor', 'middle')
            .attr('fill', 'rgba(255,255,255,0.5)')
            .text('No workflow steps defined');
    }

    function renderTree() {
        const { width, height } = getDimensions();
        const nodes = treeData.nodes;
        const links = treeData.links;

        // Calculate levels for hierarchical layout
        const nodeLevels = calculateLevels(nodes, links);
        
        // Position nodes based on levels
        positionNodes(nodes, nodeLevels, width, height);

        // Create link elements with curves
        const linkElements = treeGroup.selectAll('.link')
            .data(links)
            .enter()
            .append('path')
            .attr('class', 'link')
            .attr('marker-end', 'url(#arrowhead)')
            .attr('d', d => {
                const source = nodes.find(n => n.id === d.source);
                const target = nodes.find(n => n.id === d.target);
                if (!source || !target) return '';
                return generateCurvedPath(source, target);
            });

        // Create node groups
        const nodeGroups = treeGroup.selectAll('.node')
            .data(nodes)
            .enter()
            .append('g')
            .attr('class', 'node')
            .attr('transform', d => `translate(${d.x - config.nodeWidth/2}, ${d.y - config.nodeHeight/2})`)
            .on('click', (event, d) => showDetails(d))
            .on('mouseenter', (event, d) => showTooltip(event, d))
            .on('mouseleave', hideTooltip);

        // Node rectangles
        nodeGroups.append('rect')
            .attr('class', d => `node-rect ${d.is_entry_point ? 'node-rect-entry' : 'node-rect-regular'}`)
            .attr('width', config.nodeWidth)
            .attr('height', config.nodeHeight);

        // Step badge
        nodeGroups.append('rect')
            .attr('class', 'node-step-badge')
            .attr('x', 8)
            .attr('y', 8)
            .attr('width', 32)
            .attr('height', 20);

        nodeGroups.append('text')
            .attr('class', 'node-step-text')
            .attr('x', 24)
            .attr('y', 22)
            .text(d => `S${d.step}`);

        // Entry point indicator
        nodeGroups.filter(d => d.is_entry_point)
            .append('text')
            .attr('x', config.nodeWidth - 12)
            .attr('y', 20)
            .attr('text-anchor', 'end')
            .attr('fill', '#ffd93d')
            .attr('font-size', '14px')
            .text('★');

        // Step name
        nodeGroups.append('text')
            .attr('class', 'node-text')
            .attr('x', config.nodeWidth / 2)
            .attr('y', config.nodeHeight / 2 + 5)
            .attr('font-size', '13px')
            .text(d => truncateText(d.step_name, 22));

        // Section name
        nodeGroups.append('text')
            .attr('class', 'node-section-text')
            .attr('x', config.nodeWidth / 2)
            .attr('y', config.nodeHeight - 10)
            .text(d => truncateText(d.section_name, 28));
    }

    function calculateLevels(nodes, links) {
        const levels = {};
        const childToParents = {};
        
        // Build parent-child map
        links.forEach(link => {
            if (!childToParents[link.target]) {
                childToParents[link.target] = [];
            }
            childToParents[link.target].push(link.source);
        });

        // Find entry points (no parents or marked as entry point)
        const entryPoints = nodes.filter(n => n.is_entry_point || !childToParents[n.id]);
        entryPoints.forEach(n => levels[n.id] = 0);

        // BFS to assign levels
        let changed = true;
        while (changed) {
            changed = false;
            nodes.forEach(node => {
                if (levels[node.id] !== undefined) return;
                
                const parents = childToParents[node.id] || [];
                if (parents.length === 0) {
                    levels[node.id] = 0;
                    changed = true;
                } else if (parents.every(p => levels[p] !== undefined)) {
                    levels[node.id] = Math.max(...parents.map(p => levels[p])) + 1;
                    changed = true;
                }
            });
        }

        // Handle any remaining nodes (circular deps or orphans)
        nodes.forEach(node => {
            if (levels[node.id] === undefined) {
                levels[node.id] = 0;
            }
        });

        return levels;
    }

    function positionNodes(nodes, levels, width, height) {
        // Group nodes by level
        const nodesByLevel = {};
        nodes.forEach(node => {
            const level = levels[node.id];
            if (!nodesByLevel[level]) nodesByLevel[level] = [];
            nodesByLevel[level].push(node);
        });

        const maxLevel = Math.max(...Object.keys(nodesByLevel).map(Number));
        const startY = 100;

        // Position each level
        Object.entries(nodesByLevel).forEach(([level, levelNodes]) => {
            const y = startY + parseInt(level) * config.levelHeight;
            const totalWidth = levelNodes.length * (config.nodeWidth + config.nodePadding) - config.nodePadding;
            const startX = (width - totalWidth) / 2 + config.nodeWidth / 2;

            levelNodes.forEach((node, index) => {
                node.x = startX + index * (config.nodeWidth + config.nodePadding);
                node.y = y;
            });
        });
    }

    function generateCurvedPath(source, target) {
        const sourceX = source.x;
        const sourceY = source.y + config.nodeHeight / 2;
        const targetX = target.x;
        const targetY = target.y - config.nodeHeight / 2 - 10;

        const midY = (sourceY + targetY) / 2;

        return `M ${sourceX} ${sourceY} 
                C ${sourceX} ${midY}, ${targetX} ${midY}, ${targetX} ${targetY}`;
    }

    function truncateText(text, maxLength) {
        if (!text) return '';
        return text.length > maxLength ? text.substring(0, maxLength) + '...' : text;
    }

    function showTooltip(event, d) {
        const rect = container.getBoundingClientRect();
        tooltip.innerHTML = `
            <strong>${d.step_name}</strong><br>
            <small>${d.section_name}</small>
            ${d.is_entry_point ? '<br><span style="color: #ffd93d;">★ Entry Point</span>' : ''}
        `;
        tooltip.style.display = 'block';
        
        // Position tooltip
        const transform = d3.zoomTransform(svg.node());
        const x = transform.applyX(d.x);
        const y = transform.applyY(d.y - config.nodeHeight/2);
        
        tooltip.style.left = `${x}px`;
        tooltip.style.top = `${y - 10}px`;
    }

    function hideTooltip() {
        tooltip.style.display = 'none';
    }

    function showDetails(node) {
        document.getElementById('detailsStepBadge').textContent = `Step ${node.step}`;
        document.getElementById('detailsTitle').textContent = node.step_name;
        document.getElementById('detailsSection').textContent = node.section_name;

        let contentHtml = '';

        if (node.is_entry_point) {
            contentHtml += `
                <div class="details-row">
                    <span class="details-label">Type</span>
                    <span class="details-badge badge-entry">Entry Point</span>
                </div>
            `;
        }

        contentHtml += `
            <div class="details-row">
                <span class="details-label">Trigger</span>
                <span class="details-badge ${node.trigger === 'Manual' ? 'badge-manual' : 'badge-auto'}">${node.trigger}</span>
            </div>
        `;

        if (node.department) {
            contentHtml += `
                <div class="details-row">
                    <span class="details-label">Department</span>
                    <span class="details-value">${node.department}</span>
                </div>
            `;
        }

        if (node.checklist) {
            contentHtml += `
                <div class="details-row">
                    <span class="details-label">Checklist</span>
                    <span class="details-value">${node.checklist}</span>
                </div>
            `;
        }

        if (node.maker) {
            contentHtml += `
                <div class="details-row">
                    <span class="details-label">Maker</span>
                    <span class="details-value">${node.maker}</span>
                </div>
            `;
        }

        if (node.checker) {
            contentHtml += `
                <div class="details-row">
                    <span class="details-label">Checker</span>
                    <span class="details-value">${node.checker}</span>
                </div>
            `;
        }

        if (node.maker_tat && (node.maker_tat.days || node.maker_tat.hours)) {
            let tat = '';
            if (node.maker_tat.days) tat += `${node.maker_tat.days} days `;
            if (node.maker_tat.hours) tat += `${node.maker_tat.hours} hours`;
            contentHtml += `
                <div class="details-row">
                    <span class="details-label">Maker TAT</span>
                    <span class="details-value">${tat.trim()}</span>
                </div>
            `;
        }

        if (node.checker_tat && (node.checker_tat.days || node.checker_tat.hours)) {
            let tat = '';
            if (node.checker_tat.days) tat += `${node.checker_tat.days} days `;
            if (node.checker_tat.hours) tat += `${node.checker_tat.hours} hours`;
            contentHtml += `
                <div class="details-row">
                    <span class="details-label">Checker TAT</span>
                    <span class="details-value">${tat.trim()}</span>
                </div>
            `;
        }

        if (node.dependency_steps && node.dependency_steps.length > 0) {
            const parentNames = node.dependency_steps.map(id => {
                const parent = treeData.nodes.find(n => n.id === id);
                return parent ? `S${parent.step}` : `ID:${id}`;
            }).join(', ');
            
            contentHtml += `
                <div class="details-row">
                    <span class="details-label">Dependencies</span>
                    <span class="details-value">${parentNames}</span>
                </div>
            `;
        }

        document.getElementById('detailsContent').innerHTML = contentHtml;
        detailsPanel.classList.add('show');
    }

    function fitToScreen() {
        if (!treeData || treeData.nodes.length === 0) return;

        const { width, height } = getDimensions();
        const nodes = treeData.nodes;

        // Calculate bounds
        const minX = Math.min(...nodes.map(n => n.x)) - config.nodeWidth;
        const maxX = Math.max(...nodes.map(n => n.x)) + config.nodeWidth;
        const minY = Math.min(...nodes.map(n => n.y)) - config.nodeHeight;
        const maxY = Math.max(...nodes.map(n => n.y)) + config.nodeHeight;

        const contentWidth = maxX - minX;
        const contentHeight = maxY - minY;

        const scale = Math.min(
            (width - 100) / contentWidth,
            (height - 100) / contentHeight,
            1.5
        );

        const centerX = (minX + maxX) / 2;
        const centerY = (minY + maxY) / 2;

        const transform = d3.zoomIdentity
            .translate(width / 2, height / 2)
            .scale(scale)
            .translate(-centerX, -centerY);

        svg.transition()
            .duration(500)
            .call(zoom.transform, transform);
    }

    // Handle window resize
    window.addEventListener('resize', () => {
        if (treeData && treeData.nodes.length > 0) {
            setTimeout(fitToScreen, 100);
        }
    });
});
</script>
@endpush
