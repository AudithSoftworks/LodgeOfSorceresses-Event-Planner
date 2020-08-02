export const content = cy => {
    cy.fixture('.content.json').as('content');
    cy.route({
        method: 'GET',
        url: '/api/content',
        response: '@content'
    }).as('loadContent');
};
