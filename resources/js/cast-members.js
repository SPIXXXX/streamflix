let castRequest = 0;

document.addEventListener('click', async (event) => {
    const button = event.target.closest('[data-cast-member]');

    if (!button) {
        return;
    }

    const requestId = ++castRequest;
    const photo = document.getElementById('cast-member-photo');
    const photoPlaceholder = document.getElementById('cast-member-photo-placeholder');
    const name = document.getElementById('cast-member-name');
    const character = document.getElementById('cast-member-character');
    const department = document.getElementById('cast-member-department');
    const facts = document.getElementById('cast-member-facts');
    const biography = document.getElementById('cast-member-biography');

    if (!photo || !photoPlaceholder || !name || !character || !department || !facts || !biography) {
        return;
    }

    name.textContent = button.dataset.castName || 'Cast member';
    character.textContent = button.dataset.castCharacter || '';
    department.textContent = '';
    facts.replaceChildren();
    biography.textContent = 'Loading cast details…';
    photoPlaceholder.textContent = (button.dataset.castName || '?').charAt(0).toUpperCase();
    photo.onerror = () => {
        photo.classList.add('hidden');
        photoPlaceholder.classList.remove('hidden');
    };

    if (button.dataset.castPhoto) {
        photo.src = button.dataset.castPhoto;
        photo.alt = button.dataset.castName || '';
        photo.classList.remove('hidden');
        photoPlaceholder.classList.add('hidden');
    } else {
        photo.removeAttribute('src');
        photo.classList.add('hidden');
        photoPlaceholder.classList.remove('hidden');
    }

    try {
        const response = await fetch(button.dataset.castUrl, {
            headers: { Accept: 'application/json' },
        });
        const person = await response.json();

        if (requestId !== castRequest) {
            return;
        }

        if (!response.ok) {
            throw new Error(person.message || 'Cast details are not available.');
        }

        name.textContent = person.name || name.textContent;
        department.textContent = person.known_for_department || '';
        biography.textContent = person.biography || 'No biography is available for this cast member.';

        if (person.profile_url) {
            photo.src = person.profile_url;
            photo.alt = person.name || '';
            photo.classList.remove('hidden');
            photoPlaceholder.classList.add('hidden');
        }

        const details = [
            person.birthday ? `Born: ${person.birthday}` : null,
            person.deathday ? `Died: ${person.deathday}` : null,
            person.place_of_birth ? `Birthplace: ${person.place_of_birth}` : null,
        ].filter(Boolean);

        for (const detail of details) {
            const item = document.createElement('li');
            item.textContent = detail;
            facts.append(item);
        }
    } catch (error) {
        if (requestId === castRequest) {
            biography.textContent = error.message || 'Could not load cast details. Please try again.';
        }
    }
});
