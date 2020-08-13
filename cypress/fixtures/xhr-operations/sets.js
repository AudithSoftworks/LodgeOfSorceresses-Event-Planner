export const sets = cy => {
    cy.fixture('.sets.json').as('sets');
    cy.route({
        method: 'GET',
        url: '/api/sets',
        response: '@sets'
    }).as('loadSets');
};
