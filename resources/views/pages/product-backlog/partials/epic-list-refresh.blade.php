{{-- Update epic list on us-saved event --}}
<script>
// =================================================================================
// Listen for AI-saved US event to update epic stats without destroying chat
// =================================================================================
window.addEventListener('us-saved', async (event) => {
    console.log('🔄 US saved via AI, updating epic stats...');
    const targetEpicTitle = event.detail?.epic_title; // Epic được update
    console.log('🎯 Target epic title:', targetEpicTitle);

    try {
        const response = await fetch('/api/epics', {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
            }
        });

        if (!response.ok) throw new Error(`HTTP ${response.status}`);

        const data = await response.json();
        console.log('📡 API response for epic 5:', JSON.stringify(data.epics.find(e => e.title === 'epic 5')));
        updateEpicStats(data.epics || [], targetEpicTitle);
        console.log('✅ Epic stats updated successfully');
    } catch (error) {
        console.error('❌ Error updating epic stats:', error);
    }
});

// Update epic stats only (stories count & points) without destroying DOM
function updateEpicStats(epics, targetEpicTitle) {
    epics.forEach(epic => {
        // Find epic card in DOM by matching title
        const epicCards = document.querySelectorAll('[data-epic-list] > div');
        epicCards.forEach(card => {
            // Find h4 with epic title
            const titleEl = card.querySelector('h4.text-lg.font-semibold');
            const epicTitle = titleEl?.textContent.trim();

            if (epicTitle && epicTitle === epic.title) {
                console.log('✅ Found matching epic:', epicTitle, 'with', epic.stories_count, 'stories');
                // Find the stats container (div with ml-4 text-right)
                const statsContainer = card.querySelector('.ml-4.text-right');
                console.log('📊 Stats container found:', !!statsContainer, 'for', epicTitle);

                if (statsContainer) {
                    // Update stories count (first div inside stats container)
                    const countEl = statsContainer.querySelector('.text-sm.text-gray-500');
                    console.log('   📝 Count element:', !!countEl, 'current:', countEl?.textContent);
                    if (countEl) {
                        const newText = `${epic.stories_count} stories`;
                        console.log('   ✏️ Updating to:', newText);
                        countEl.textContent = newText;
                        countEl.style.color = '#ef4444'; // Force repaint with red color
                        setTimeout(() => countEl.style.color = '', 100); // Reset after 100ms
                    }

                    // Update points (second div inside stats container)
                    const pointsEl = statsContainer.querySelector('.text-sm.text-gray-700.font-medium');
                    console.log('   💯 Points element:', !!pointsEl, 'current:', pointsEl?.textContent);
                    if (pointsEl) {
                        const newPoints = `${epic.total_points} pts`;
                        console.log('   ✏️ Updating to:', newPoints);
                        pointsEl.textContent = newPoints;
                    }
                } else {
                    console.warn('⚠️ Stats container NOT found for:', epicTitle);
                }                // 🔥 Automatically expand ONLY the target epic that was updated
                if (targetEpicTitle && epicTitle === targetEpicTitle) {
                    console.log(`✅ Epic ${epic.title} updated, reloading page to show new US...`);
                    // Trigger save chat before reload (if chat widget exists)
                    if (window.saveChatHistory) {
                        window.saveChatHistory();
                    }
                    setTimeout(() => window.location.reload(), 500);
                }
            }
        });
    });
}
</script>
