openapi: 3.0.0
...
# 1) Define the security scheme type (HTTP bearer)
components:
securitySchemes:
bearerAuth:            # arbitrary name for the security scheme
type: http
scheme: bearer
bearerFormat: JWT    # optional, arbitrary value for documentation purposes
# 2) Apply the security globally to all operations
security:
- bearerAuth: []         # use the same name as above