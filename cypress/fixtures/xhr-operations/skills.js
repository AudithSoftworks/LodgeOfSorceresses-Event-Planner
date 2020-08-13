export const skills = cy => {
    cy.fixture('.skills.json').as('skills');
    cy.route({
        method: 'GET',
        url: '/api/skills',
        response: '@skills'
    }).as('loadSkills');
};
