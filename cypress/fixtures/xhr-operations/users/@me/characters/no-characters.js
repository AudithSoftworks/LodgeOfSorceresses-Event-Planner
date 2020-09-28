export const noCharacters = cy => {
    cy.fixture('xhr-data/characters/no-characters.json').as('noCharacters');
    cy.route({
        method: 'GET',
        url: '/api/users/@me/characters',
        delay: 4000,
        response: '@noCharacters',
    }).as('loadCharacters');
};
