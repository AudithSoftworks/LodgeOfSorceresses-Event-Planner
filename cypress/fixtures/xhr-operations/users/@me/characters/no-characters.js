export const noCharacters = cy => {
    cy.fixture('xhr-data/characters/no-characters.json').as('noCharacters');
    cy.route('GET', '/api/users/@me/characters', '@noCharacters');
};
